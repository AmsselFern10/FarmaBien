<?php

namespace App\Services;

use App\Models\Producto;
use App\Models\PrecioVenta;
use App\Models\PresentacionProducto;
use App\Models\AuditLog;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PrecioVentaService
{
    public function __construct(
        protected PromocionService $promocionService
    ) {}

    /**
     * Obtiene el listado de productos con precios, relaciones y filtros de margen.
     */
    public function listarProductosConPrecios(array $filtros = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Producto::with([
            'categoria:id,nombre',
            'laboratorio:id,nombre',
            'presentaciones' => function ($q) {
                $q->where('activo', true)->orderBy('orden', 'asc');
            },
            'precioVentaVigente.usuario:id,name',
        ])->whereNull('deleted_at');

        if (!empty($filtros['q'])) {
            $q = trim($filtros['q']);
            $query->where(function ($sub) use ($q) {
                $sub->where('nombre', 'like', "%{$q}%")
                    ->orWhere('codigo_barra', 'like', "%{$q}%")
                    ->orWhere('principio_activo', 'like', "%{$q}%");
            });
        }

        if (!empty($filtros['categoria_id'])) {
            $catIds = is_array($filtros['categoria_id']) ? $filtros['categoria_id'] : explode(',', $filtros['categoria_id']);
            $query->whereIn('categoria_id', array_filter($catIds));
        }

        if (!empty($filtros['laboratorio_id'])) {
            $labIds = is_array($filtros['laboratorio_id']) ? $filtros['laboratorio_id'] : explode(',', $filtros['laboratorio_id']);
            $query->whereIn('laboratorio_id', array_filter($labIds));
        }

        if (!empty($filtros['proveedor_id'])) {
            $provIds = is_array($filtros['proveedor_id']) ? $filtros['proveedor_id'] : explode(',', $filtros['proveedor_id']);
            $query->whereHas('detallesCompras.compra', function ($cq) use ($provIds) {
                $cq->whereIn('proveedor_id', array_filter($provIds));
            });
        }

        if (!empty($filtros['estado_margen'])) {
            if ($filtros['estado_margen'] === 'negativo') {
                $query->whereRaw('precio_venta < precio_compra');
            } elseif ($filtros['estado_margen'] === 'bajo') {
                $query->whereRaw('precio_venta >= precio_compra AND ((precio_venta - precio_compra) / NULLIF(precio_venta, 0)) * 100 < 25');
            } elseif ($filtros['estado_margen'] === 'saludable') {
                $query->whereRaw('precio_venta > precio_compra AND ((precio_venta - precio_compra) / NULLIF(precio_venta, 0)) * 100 >= 25');
            }
        }

        return $query->orderBy('nombre', 'asc')->paginate($perPage);
    }

    /**
     * Obtiene las métricas clave de precios y márgenes comerciales en una única consulta optimizada.
     */
    public function obtenerMetricasPrecios(): array
    {
        return Cache::remember('precio_metricas_v1', 900, function () {
            $stats = Producto::whereNull('deleted_at')
                ->where('precio_venta', '>', 0)
                ->selectRaw('
                    COUNT(*) as total_con_precio,
                    AVG(CASE WHEN precio_compra > 0 THEN ((precio_venta - precio_compra) / precio_venta) * 100 ELSE NULL END) as margen_prom,
                    COUNT(CASE WHEN ((precio_venta - COALESCE(precio_compra, 0)) / NULLIF(precio_venta, 0)) * 100 < 25 THEN 1 ELSE NULL END) as productos_margen_bajo
                ')
                ->first();

            $cambiosUltimos30Dias = PrecioVenta::where('vigente_desde', '>=', now()->subDays(30))->count();

            return [
                'totalProductosConPrecio' => (int)($stats->total_con_precio ?? 0),
                'margenPromedio'          => round((float)($stats->margen_prom ?? 0), 1),
                'productosMargenBajo'     => (int)($stats->productos_margen_bajo ?? 0),
                'cambiosUltimos30Dias'    => (int)$cambiosUltimos30Dias,
            ];
        });
    }

    /**
     * Obtiene el listado paginado del historial general de auditoría de precios.
     */
    public function listarHistorialAuditoria(array $filtros = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = PrecioVenta::with([
            'producto.categoria:id,nombre',
            'producto.laboratorio:id,nombre',
            'presentacion',
            'usuario:id,name'
        ]);

        if (!empty($filtros['q'])) {
            $q = trim($filtros['q']);
            $query->whereHas('producto', function ($pq) use ($q) {
                $pq->where('nombre', 'like', "%{$q}%")
                   ->orWhere('codigo_barra', 'like', "%{$q}%")
                   ->orWhere('principio_activo', 'like', "%{$q}%");
            });
        }

        if (!empty($filtros['desde'])) {
            $query->whereDate('vigente_desde', '>=', $filtros['desde']);
        }

        if (!empty($filtros['hasta'])) {
            $query->whereDate('vigente_desde', '<=', $filtros['hasta']);
        }

        if (!empty($filtros['user_id'])) {
            $query->where('user_id', (int)$filtros['user_id']);
        }

        return $query->orderByDesc('vigente_desde')->orderByDesc('id')->paginate($perPage);
    }

    /**
     * Búsqueda ágil de autocompletado para el módulo de precios.
     */
    public function buscarParaAutocompletar(string $tipo, string $query, int $limit = 10): array
    {
        $q = trim($query);
        if (strlen($q) < 2) {
            return [];
        }

        if ($tipo === 'categoria') {
            return app(CategoriaService::class)->buscarAjax($q, $limit);
        } elseif ($tipo === 'laboratorio') {
            return app(LaboratorioService::class)->buscarAjax($q, $limit);
        } elseif ($tipo === 'proveedor') {
            return app(ProveedorService::class)->buscarAjax($q, $limit);
        } else {
            return Producto::whereNull('deleted_at')
                ->where(function ($sub) use ($q) {
                    $sub->where('nombre', 'like', "%{$q}%")
                        ->orWhere('codigo_barra', 'like', "%{$q}%")
                        ->orWhere('principio_activo', 'like', "%{$q}%");
                })
                ->limit($limit)
                ->get(['id', 'nombre', 'codigo_barra', 'precio_venta', 'precio_compra'])
                ->map(fn($item) => [
                    'id'            => $item->id,
                    'text'          => $item->nombre . ($item->codigo_barra ? " ({$item->codigo_barra})" : ""),
                    'nombre'        => $item->nombre,
                    'precio_venta'  => (float)$item->precio_venta,
                    'precio_compra' => (float)$item->precio_compra,
                ])
                ->toArray();
        }
    }

    /**
     * Actualiza el precio de venta base y presentaciones de un producto cerrando vigencias previas.
     */
    public function actualizarPreciosProducto(Producto $producto, float $nuevoPrecioBase, string $motivo, ?string $fechaVigencia = null, array $presentaciones = []): void
    {
        $usuarioId = Auth::id();
        $fechaVigencia = $fechaVigencia ? $fechaVigencia : now();
        $nuevoPrecioBase = round($nuevoPrecioBase, 2);
        $motivo = trim($motivo);

        DB::transaction(function () use ($producto, $nuevoPrecioBase, $fechaVigencia, $motivo, $usuarioId, $presentaciones) {
            $precioBaseAnterior = (float)($producto->precio_venta ?? 0);

            // 1. Actualizar Precio Base si cambió
            if (abs($nuevoPrecioBase - $precioBaseAnterior) >= 0.01) {
                PrecioVenta::where('producto_id', $producto->id)
                    ->whereNull('presentacion_id')
                    ->whereNull('vigente_hasta')
                    ->update(['vigente_hasta' => $fechaVigencia]);

                PrecioVenta::create([
                    'producto_id'     => $producto->id,
                    'presentacion_id' => null,
                    'precio'          => $nuevoPrecioBase,
                    'vigente_desde'   => $fechaVigencia,
                    'vigente_hasta'   => null,
                    'motivo'          => $motivo,
                    'user_id'         => $usuarioId,
                ]);

                $producto->update(['precio_venta' => $nuevoPrecioBase]);

                // Sincronizar automáticamente la presentación de unidad base
                $unidadBase = PresentacionProducto::where('producto_id', $producto->id)->where('es_unidad_base', true)->first();
                if ($unidadBase) {
                    $unidadBase->update(['precio_venta' => $nuevoPrecioBase]);

                    PrecioVenta::where('producto_id', $producto->id)
                        ->where('presentacion_id', $unidadBase->id)
                        ->whereNull('vigente_hasta')
                        ->update(['vigente_hasta' => $fechaVigencia]);

                    PrecioVenta::create([
                        'producto_id'     => $producto->id,
                        'presentacion_id' => $unidadBase->id,
                        'precio'          => $nuevoPrecioBase,
                        'vigente_desde'   => $fechaVigencia,
                        'vigente_hasta'   => null,
                        'motivo'          => $motivo . " (Unidad Base sincronizada)",
                        'user_id'         => $usuarioId,
                    ]);
                }
            }

            // 2. Actualizar Precios de Presentaciones
            if (!empty($presentaciones)) {
                foreach ($presentaciones as $presData) {
                    $presId = (int)$presData['id'];
                    $nuevoPrecioPres = round((float)$presData['precio_venta'], 2);

                    $presentacion = PresentacionProducto::where('producto_id', $producto->id)->where('id', $presId)->first();
                    if ($presentacion) {
                        $precioPresAnterior = (float)($presentacion->precio_venta ?? 0);

                        if (abs($nuevoPrecioPres - $precioPresAnterior) >= 0.01) {
                            PrecioVenta::where('producto_id', $producto->id)
                                ->where('presentacion_id', $presId)
                                ->whereNull('vigente_hasta')
                                ->update(['vigente_hasta' => $fechaVigencia]);

                            PrecioVenta::create([
                                'producto_id'     => $producto->id,
                                'presentacion_id' => $presId,
                                'precio'          => $nuevoPrecioPres,
                                'vigente_desde'   => $fechaVigencia,
                                'vigente_hasta'   => null,
                                'motivo'          => $motivo . " (Presentación: {$presentacion->nombre})",
                                'user_id'         => $usuarioId,
                            ]);

                            $presentacion->update(['precio_venta' => $nuevoPrecioPres]);
                        }
                    }
                }
            }

            AuditLog::log('precios', 'actualizar', "Precio de venta actualizado para {$producto->nombre} a C$ {$nuevoPrecioBase}", [
                'producto_id'     => $producto->id,
                'precio_anterior' => $precioBaseAnterior,
                'precio_nuevo'    => $nuevoPrecioBase,
                'motivo'          => $motivo,
            ]);
        });
    }

    /**
     * Actualiza el precio en línea (AJAX) desde la tabla de precios.
     */
    public function actualizarPrecioInline(Producto $producto, float $nuevoPrecio, string $motivo): array
    {
        $nuevoPrecio = round($nuevoPrecio, 2);
        $motivo = trim($motivo);
        $usuarioId = Auth::id();
        $ahora = now();

        DB::transaction(function () use ($producto, $nuevoPrecio, $motivo, $usuarioId, $ahora) {
            PrecioVenta::where('producto_id', $producto->id)
                ->whereNull('presentacion_id')
                ->whereNull('vigente_hasta')
                ->update(['vigente_hasta' => $ahora]);

            PrecioVenta::create([
                'producto_id'     => $producto->id,
                'presentacion_id' => null,
                'precio'          => $nuevoPrecio,
                'vigente_desde'   => $ahora,
                'vigente_hasta'   => null,
                'motivo'          => $motivo,
                'user_id'         => $usuarioId,
            ]);

            $producto->update(['precio_venta' => $nuevoPrecio]);

            // Sincronizar automáticamente la unidad base
            $unidadBase = PresentacionProducto::where('producto_id', $producto->id)->where('es_unidad_base', true)->first();
            if ($unidadBase) {
                $unidadBase->update(['precio_venta' => $nuevoPrecio]);

                PrecioVenta::where('producto_id', $producto->id)
                    ->where('presentacion_id', $unidadBase->id)
                    ->whereNull('vigente_hasta')
                    ->update(['vigente_hasta' => $ahora]);

                PrecioVenta::create([
                    'producto_id'     => $producto->id,
                    'presentacion_id' => $unidadBase->id,
                    'precio'          => $nuevoPrecio,
                    'vigente_desde'   => $ahora,
                    'vigente_hasta'   => null,
                    'motivo'          => $motivo . " (Unidad Base sincronizada)",
                    'user_id'         => $usuarioId,
                ]);
            }
        });

        $costo = (float)($producto->precio_compra ?? 0);
        $nuevoMargen = $nuevoPrecio > 0 ? round((($nuevoPrecio - $costo) / $nuevoPrecio) * 100, 1) : 0;

        return [
            'success'          => true,
            'message'          => "Precio actualizado a C$ " . number_format($nuevoPrecio, 2),
            'producto_id'      => $producto->id,
            'precio_nuevo'     => $nuevoPrecio,
            'margen_nuevo'     => $nuevoMargen,
            'usuario_nombre'   => Auth::user()->name ?? 'Usuario',
            'fecha_formateada' => $ahora->format('d/m/Y H:i'),
        ];
    }

    /**
     * Genera la simulación de vista previa para actualización masiva de precios.
     */
    public function generarVistaPreviaMasiva(string $tipoAlcance, array $alcanceIds, string $tipoAjuste, float $valorAjuste, string $redondeo = 'sin'): array
    {
        $query = Producto::with(['categoria:id,nombre', 'laboratorio:id,nombre'])
            ->whereNull('deleted_at')
            ->where('precio_venta', '>', 0);

        if ($tipoAlcance === 'categoria' && !empty($alcanceIds)) {
            $query->whereIn('categoria_id', $alcanceIds);
        } elseif ($tipoAlcance === 'laboratorio' && !empty($alcanceIds)) {
            $query->whereIn('laboratorio_id', $alcanceIds);
        } elseif ($tipoAlcance === 'proveedor' && !empty($alcanceIds)) {
            $query->whereHas('detallesCompras.compra', function ($cq) use ($alcanceIds) {
                $cq->whereIn('proveedor_id', $alcanceIds);
            });
        } elseif ($tipoAlcance === 'productos' && !empty($alcanceIds)) {
            $query->whereIn('id', $alcanceIds);
        }

        $productos = $query->orderBy('nombre', 'asc')->get();

        $preview = [];
        $totalAfectados = 0;
        $totalMargenNegativo = 0;

        foreach ($productos as $prod) {
            $precioActual = (float)$prod->precio_venta;
            $costo = (float)($prod->precio_compra ?? 0);
            $precioNuevo = $precioActual;

            if ($tipoAjuste === 'porcentaje_aumento') {
                $precioNuevo = $precioActual * (1 + ($valorAjuste / 100));
            } elseif ($tipoAjuste === 'porcentaje_disminucion') {
                $precioNuevo = $precioActual * (1 - ($valorAjuste / 100));
            } elseif ($tipoAjuste === 'monto_aumento') {
                $precioNuevo = $precioActual + $valorAjuste;
            } elseif ($tipoAjuste === 'monto_disminucion') {
                $precioNuevo = max(0.01, $precioActual - $valorAjuste);
            } elseif ($tipoAjuste === 'fijo') {
                $precioNuevo = $valorAjuste;
            }

            // Aplicar redondeo
            if ($redondeo === '0.05') {
                $precioNuevo = round($precioNuevo / 0.05) * 0.05;
            } elseif ($redondeo === 'unidad') {
                $precioNuevo = round($precioNuevo);
            } else {
                $precioNuevo = round($precioNuevo, 2);
            }

            $precioNuevo = max(0.01, $precioNuevo);
            $diferencia = round($precioNuevo - $precioActual, 2);
            $margenNuevo = $precioNuevo > 0 ? round((($precioNuevo - $costo) / $precioNuevo) * 100, 1) : 0;
            $esNegativo = $precioNuevo < $costo;

            if ($esNegativo) {
                $totalMargenNegativo++;
            }

            $totalAfectados++;

            $preview[] = [
                'id'            => $prod->id,
                'nombre'        => $prod->nombre,
                'codigo_barra'  => $prod->codigo_barra ?? 'S/C',
                'categoria'     => $prod->categoria->nombre ?? 'Sin categoría',
                'laboratorio'   => $prod->laboratorio->nombre ?? 'Sin laboratorio',
                'costo'         => $costo,
                'precio_actual' => $precioActual,
                'precio_nuevo'  => $precioNuevo,
                'diferencia'    => $diferencia,
                'margen_nuevo'  => $margenNuevo,
                'es_negativo'   => $esNegativo,
            ];
        }

        return [
            'total_afectados'       => $totalAfectados,
            'total_margen_negativo' => $totalMargenNegativo,
            'items'                 => $preview,
        ];
    }

    /**
     * Aplica el ajuste masivo de precios de forma transaccional y registra el historial.
     */
    public function aplicarAjusteMasivo(string $tipoAlcance, array $alcanceIds, string $tipoAjuste, float $valorAjuste, string $redondeo, string $motivo): array
    {
        $previewData = $this->generarVistaPreviaMasiva($tipoAlcance, $alcanceIds, $tipoAjuste, $valorAjuste, $redondeo);
        $usuarioId = Auth::id();
        $ahora = now();
        $actualizados = 0;

        DB::transaction(function () use ($previewData, $motivo, $usuarioId, $ahora, &$actualizados) {
            foreach ($previewData['items'] as $item) {
                $prodId = $item['id'];
                $nuevoPrecio = $item['precio_nuevo'];
                $precioActual = $item['precio_actual'];

                if (abs($nuevoPrecio - $precioActual) < 0.01) {
                    continue;
                }

                // Cerrar precio base vigente previo
                PrecioVenta::where('producto_id', $prodId)
                    ->whereNull('presentacion_id')
                    ->whereNull('vigente_hasta')
                    ->update(['vigente_hasta' => $ahora]);

                // Registrar nuevo precio en historial
                PrecioVenta::create([
                    'producto_id'     => $prodId,
                    'presentacion_id' => null,
                    'precio'          => $nuevoPrecio,
                    'vigente_desde'   => $ahora,
                    'vigente_hasta'   => null,
                    'motivo'          => $motivo,
                    'user_id'         => $usuarioId,
                ]);

                // Actualizar producto
                Producto::where('id', $prodId)->update(['precio_venta' => $nuevoPrecio]);

                // Sincronizar automáticamente unidad base
                $unidadBase = PresentacionProducto::where('producto_id', $prodId)->where('es_unidad_base', true)->first();
                if ($unidadBase) {
                    $unidadBase->update(['precio_venta' => $nuevoPrecio]);

                    PrecioVenta::where('producto_id', $prodId)
                        ->where('presentacion_id', $unidadBase->id)
                        ->whereNull('vigente_hasta')
                        ->update(['vigente_hasta' => $ahora]);

                    PrecioVenta::create([
                        'producto_id'     => $prodId,
                        'presentacion_id' => $unidadBase->id,
                        'precio'          => $nuevoPrecio,
                        'vigente_desde'   => $ahora,
                        'vigente_hasta'   => null,
                        'motivo'          => $motivo . " (Unidad Base sincronizada)",
                        'user_id'         => $usuarioId,
                    ]);
                }

                $actualizados++;
            }

            AuditLog::log('precios', 'ajuste_masivo', "Actualización masiva de precios aplicada: {$actualizados} productos actualizados", [
                'productos_afectados' => $actualizados,
                'motivo'              => $motivo,
            ]);
        });

        return [
            'success'               => true,
            'productos_actualizados'=> $actualizados,
            'message'               => "Se actualizaron con éxito los precios de {$actualizados} productos.",
        ];
    }
}
