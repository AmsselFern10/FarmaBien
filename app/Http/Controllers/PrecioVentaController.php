<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\PrecioVenta;
use App\Models\PresentacionProducto;
use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Models\Proveedor;
use App\Models\Promocion;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PrecioVentaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Listado principal de Precios de Venta con métricas, filtros y edición rápida
     */
    public function index(Request $request)
    {
        $query = Producto::with([
            'categoria:id,nombre',
            'laboratorio:id,nombre',
            'presentaciones' => function ($q) {
                $q->where('activo', true)->orderBy('orden', 'asc');
            },
            'precioVentaVigente.usuario:id,name',
        ])->whereNull('deleted_at');

        // Búsqueda por texto (Nombre, Código, Principio Activo)
        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($sub) use ($q) {
                $sub->where('nombre', 'like', "%{$q}%")
                    ->orWhere('codigo_barra', 'like', "%{$q}%")
                    ->orWhere('principio_activo', 'like', "%{$q}%");
            });
        }

        // Filtro por Categoría
        if ($request->filled('categoria_id')) {
            $catIds = is_array($request->categoria_id) ? $request->categoria_id : explode(',', $request->categoria_id);
            $query->whereIn('categoria_id', array_filter($catIds));
        }

        // Filtro por Laboratorio
        if ($request->filled('laboratorio_id')) {
            $labIds = is_array($request->laboratorio_id) ? $request->laboratorio_id : explode(',', $request->laboratorio_id);
            $query->whereIn('laboratorio_id', array_filter($labIds));
        }

        // Filtro por Proveedor (a través de compras históricas)
        if ($request->filled('proveedor_id')) {
            $provIds = is_array($request->proveedor_id) ? $request->proveedor_id : explode(',', $request->proveedor_id);
            $query->whereHas('detallesCompras.compra', function ($cq) use ($provIds) {
                $cq->whereIn('proveedor_id', array_filter($provIds));
            });
        }

        // Filtro por Estado de Margen (saludable >= 25%, bajo 0..24.9%, negativo < 0%)
        if ($request->filled('estado_margen')) {
            if ($request->estado_margen === 'negativo') {
                $query->whereRaw('precio_venta < precio_compra');
            } elseif ($request->estado_margen === 'bajo') {
                $query->whereRaw('precio_venta >= precio_compra AND ((precio_venta - precio_compra) / NULLIF(precio_venta, 0)) * 100 < 25');
            } elseif ($request->estado_margen === 'saludable') {
                $query->whereRaw('precio_venta > precio_compra AND ((precio_venta - precio_compra) / NULLIF(precio_venta, 0)) * 100 >= 25');
            }
        }

        // Métricas de Cabecera
        $totalProductosConPrecio = Producto::whereNull('deleted_at')->where('precio_venta', '>', 0)->count();

        $margenPromedio = Producto::whereNull('deleted_at')
            ->where('precio_venta', '>', 0)
            ->where('precio_compra', '>', 0)
            ->selectRaw('AVG(((precio_venta - precio_compra) / precio_venta) * 100) as margen_prom')
            ->value('margen_prom') ?? 0;

        $productosMargenBajo = Producto::whereNull('deleted_at')
            ->where('precio_venta', '>', 0)
            ->whereRaw('((precio_venta - precio_compra) / NULLIF(precio_venta, 0)) * 100 < 25')
            ->count();

        $cambiosUltimos30Dias = PrecioVenta::where('vigente_desde', '>=', now()->subDays(30))->count();

        $perPage = in_array((int)$request->per_page, [15, 25, 50, 100]) ? (int)$request->per_page : 15;
        $productos = $query->orderBy('nombre', 'asc')->paginate($perPage)->withQueryString();

        // Cargar promociones activas vigentes para mostrar badges en la tabla
        $promocionesActivas = [];
        if (class_exists(Promocion::class)) {
            $promocionesActivas = Promocion::where('activo', true)
                ->where(function ($q) {
                    $q->whereNull('fecha_inicio')->orWhere('fecha_inicio', '<=', now());
                })
                ->where(function ($q) {
                    $q->whereNull('fecha_fin')->orWhere('fecha_fin', '>=', now());
                })
                ->get();
        }

        return view('precios.index', compact(
            'productos',
            'totalProductosConPrecio',
            'margenPromedio',
            'productosMargenBajo',
            'cambiosUltimos30Dias',
            'promocionesActivas'
        ));
    }

    /**
     * Ficha de Precio de Venta (Show) con historial de cambios y presentaciones
     */
    public function show(Producto $producto)
    {
        $producto->load([
            'categoria',
            'laboratorio',
            'presentaciones' => function ($q) {
                $q->where('activo', true)->orderBy('orden', 'asc');
            },
            'precioVentaVigente.usuario',
        ]);

        // Historial completo de cambios de precios (base y presentaciones)
        $historial = PrecioVenta::with(['presentacion', 'usuario'])
            ->where('producto_id', $producto->id)
            ->orderByDesc('vigente_desde')
            ->orderByDesc('id')
            ->get();

        // Buscar promoción activa vigente que aplique al producto
        $promocionVigente = null;
        if (class_exists(Promocion::class)) {
            $promocionVigente = Promocion::where('activo', true)
                ->where(function ($q) {
                    $q->whereNull('fecha_inicio')->orWhere('fecha_inicio', '<=', now());
                })
                ->where(function ($q) {
                    $q->whereNull('fecha_fin')->orWhere('fecha_fin', '>=', now());
                })
                ->where(function ($q) use ($producto) {
                    $q->where('producto_id', $producto->id)
                        ->orWhere('alcance', 'general');
                    if ($producto->categoria_id) {
                        $q->orWhere('categoria_id', $producto->categoria_id);
                    }
                    if ($producto->laboratorio_id) {
                        $q->orWhere('laboratorio_id', $producto->laboratorio_id);
                    }
                })
                ->first();
        }

        // Costo de referencia
        $costoReferencia = (float)($producto->precio_compra ?? 0);
        $precioBase = (float)($producto->precio_venta ?? 0);
        $margenEstimado = $precioBase > 0 ? (($precioBase - $costoReferencia) / $precioBase) * 100 : 0;

        return view('precios.show', compact(
            'producto',
            'historial',
            'promocionVigente',
            'costoReferencia',
            'precioBase',
            'margenEstimado'
        ));
    }

    /**
     * Formulario de Edición de Precio de Venta (Edit)
     */
    public function edit(Producto $producto)
    {
        $producto->load([
            'categoria',
            'laboratorio',
            'presentaciones' => function ($q) {
                $q->where('activo', true)->orderBy('orden', 'asc');
            },
            'precioVentaVigente',
        ]);

        $costoReferencia = (float)($producto->precio_compra ?? 0);
        $precioBase = (float)($producto->precio_venta ?? 0);

        return view('precios.edit', compact('producto', 'costoReferencia', 'precioBase'));
    }

    /**
     * Guardar cambios de precio de venta (Update)
     */
    public function update(Request $request, Producto $producto)
    {
        $request->validate([
            'precio_base' => 'required|numeric|min:0.01',
            'motivo'      => 'required|string|min:3|max:500',
            'vigente_desde' => 'nullable|date',
            'presentaciones' => 'nullable|array',
            'presentaciones.*.id' => 'required|integer',
            'presentaciones.*.precio_venta' => 'required|numeric|min:0.01',
        ], [
            'precio_base.required' => 'El precio de venta base es obligatorio.',
            'precio_base.min' => 'El precio de venta base debe ser mayor a 0.',
            'motivo.required' => 'El motivo del cambio de precio es obligatorio para fines de auditoría.',
            'motivo.min' => 'Indique un motivo claro de al menos 3 caracteres.',
        ]);

        $usuarioId = Auth::id();
        $fechaVigencia = $request->filled('vigente_desde') ? $request->vigente_desde : now();
        $nuevoPrecioBase = round((float)$request->precio_base, 2);
        $motivo = trim($request->motivo);

        DB::transaction(function () use ($producto, $nuevoPrecioBase, $fechaVigencia, $motivo, $usuarioId, $request) {
            $precioBaseAnterior = (float)($producto->precio_venta ?? 0);

            // 1. Actualizar Precio Base si cambió
            if (abs($nuevoPrecioBase - $precioBaseAnterior) >= 0.01) {
                // Cerrar precio base vigente anterior
                PrecioVenta::where('producto_id', $producto->id)
                    ->whereNull('presentacion_id')
                    ->whereNull('vigente_hasta')
                    ->update(['vigente_hasta' => $fechaVigencia]);

                // Crear nuevo registro de precio base vigente
                PrecioVenta::create([
                    'producto_id'     => $producto->id,
                    'presentacion_id' => null,
                    'precio'          => $nuevoPrecioBase,
                    'vigente_desde'   => $fechaVigencia,
                    'vigente_hasta'   => null,
                    'motivo'          => $motivo,
                    'user_id'         => $usuarioId,
                ]);

                // Actualizar tabla productos
                $producto->update(['precio_venta' => $nuevoPrecioBase]);
            }

            // 2. Actualizar Precios de Presentaciones
            if (!empty($request->presentaciones)) {
                foreach ($request->presentaciones as $presData) {
                    $presId = (int)$presData['id'];
                    $nuevoPrecioPres = round((float)$presData['precio_venta'], 2);

                    $presentacion = PresentacionProducto::where('producto_id', $producto->id)->where('id', $presId)->first();
                    if ($presentacion) {
                        $precioPresAnterior = (float)($presentacion->precio_venta ?? 0);

                        if (abs($nuevoPrecioPres - $precioPresAnterior) >= 0.01) {
                            // Cerrar precio vigente anterior
                            PrecioVenta::where('producto_id', $producto->id)
                                ->where('presentacion_id', $presId)
                                ->whereNull('vigente_hasta')
                                ->update(['vigente_hasta' => $fechaVigencia]);

                            // Crear nuevo registro de precio de presentación
                            PrecioVenta::create([
                                'producto_id'     => $producto->id,
                                'presentacion_id' => $presId,
                                'precio'          => $nuevoPrecioPres,
                                'vigente_desde'   => $fechaVigencia,
                                'vigente_hasta'   => null,
                                'motivo'          => $motivo . " (Presentación: {$presentacion->nombre})",
                                'user_id'         => $usuarioId,
                            ]);

                            // Actualizar tabla presentaciones_producto
                            $presentacion->update(['precio_venta' => $nuevoPrecioPres]);
                        }
                    }
                }
            }

            // Log de auditoría
            if (class_exists(AuditLog::class)) {
                AuditLog::log('precios', 'actualizar', "Precio de venta actualizado para {$producto->nombre} a C$ {$nuevoPrecioBase}", [
                    'producto_id' => $producto->id,
                    'precio_anterior' => $precioBaseAnterior,
                    'precio_nuevo' => $nuevoPrecioBase,
                    'motivo' => $motivo,
                ]);
            }
        });

        return redirect()->route('precios.show', $producto)
            ->with('success', "Precios de venta actualizados correctamente para {$producto->nombre}.");
    }

    /**
     * Edición Rápida en Línea desde la tabla del Index (AJAX)
     */
    public function inlineUpdate(Request $request, Producto $producto)
    {
        $request->validate([
            'precio_venta' => 'required|numeric|min:0.01',
            'motivo'       => 'required|string|min:3|max:300',
        ]);

        $nuevoPrecio = round((float)$request->precio_venta, 2);
        $motivo = trim($request->motivo);
        $usuarioId = Auth::id();
        $ahora = now();

        $precioAnterior = (float)($producto->precio_venta ?? 0);

        DB::transaction(function () use ($producto, $nuevoPrecio, $motivo, $usuarioId, $ahora) {
            // Cerrar precio anterior
            PrecioVenta::where('producto_id', $producto->id)
                ->whereNull('presentacion_id')
                ->whereNull('vigente_hasta')
                ->update(['vigente_hasta' => $ahora]);

            // Crear nuevo
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
        });

        $costo = (float)($producto->precio_compra ?? 0);
        $nuevoMargen = $nuevoPrecio > 0 ? round((($nuevoPrecio - $costo) / $nuevoPrecio) * 100, 1) : 0;

        return response()->json([
            'success' => true,
            'message' => "Precio actualizado a C$ " . number_format($nuevoPrecio, 2),
            'producto_id' => $producto->id,
            'precio_nuevo' => $nuevoPrecio,
            'margen_nuevo' => $nuevoMargen,
            'usuario_nombre' => Auth::user()->name ?? 'Usuario',
            'fecha_formateada' => $ahora->format('d/m/Y H:i'),
        ]);
    }

    /**
     * Vista de Actualización Masiva de Precios
     */
    public function masivo()
    {
        $categorias = Categoria::activas()->orderBy('nombre')->get(['id', 'nombre']);
        $laboratorios = Laboratorio::activos()->orderBy('nombre')->get(['id', 'nombre']);
        $proveedores = Proveedor::activos()->orderBy('nombre')->get(['id', 'nombre']);

        return view('precios.masivo', compact('categorias', 'laboratorios', 'proveedores'));
    }

    /**
     * API Vista Previa en Vivo para Actualización Masiva
     */
    public function apiPreviewMasivo(Request $request)
    {
        $tipoAlcance = $request->get('tipo_alcance', 'todo'); // 'todo', 'categoria', 'laboratorio', 'proveedor', 'productos'
        $alcanceIds = $request->get('alcance_ids', []);
        if (is_string($alcanceIds)) {
            $alcanceIds = array_filter(explode(',', $alcanceIds));
        }

        $tipoAjuste = $request->get('tipo_ajuste', 'porcentaje_aumento'); // porcentaje_aumento, porcentaje_disminucion, monto_aumento, monto_disminucion, fijo
        $valorAjuste = (float)$request->get('valor_ajuste', 0);
        $redondeo = $request->get('redondeo', 'sin'); // sin, 0.05, unidad

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
                'id' => $prod->id,
                'nombre' => $prod->nombre,
                'codigo_barra' => $prod->codigo_barra ?? 'S/C',
                'categoria' => $prod->categoria->nombre ?? 'Sin categoría',
                'laboratorio' => $prod->laboratorio->nombre ?? 'Sin laboratorio',
                'costo' => $costo,
                'precio_actual' => $precioActual,
                'precio_nuevo' => $precioNuevo,
                'diferencia' => $diferencia,
                'margen_nuevo' => $margenNuevo,
                'es_negativo' => $esNegativo,
            ];
        }

        return response()->json([
            'success' => true,
            'total_afectados' => $totalAfectados,
            'total_margen_negativo' => $totalMargenNegativo,
            'productos' => $preview,
        ]);
    }

    /**
     * Procesar la Actualización Masiva de Precios
     */
    public function aplicarMasivo(Request $request)
    {
        $request->validate([
            'tipo_alcance' => 'required|in:todo,categoria,laboratorio,proveedor,productos',
            'tipo_ajuste'  => 'required|in:porcentaje_aumento,porcentaje_disminucion,monto_aumento,monto_disminucion,fijo',
            'valor_ajuste' => 'required|numeric|min:0.01',
            'redondeo'     => 'required|in:sin,0.05,unidad',
            'motivo'       => 'required|string|min:3|max:500',
            'aplicar_presentaciones' => 'nullable|boolean',
        ]);

        $tipoAlcance = $request->tipo_alcance;
        $alcanceIds = $request->get('alcance_ids', []);
        if (is_string($alcanceIds)) {
            $alcanceIds = array_filter(explode(',', $alcanceIds));
        }

        $tipoAjuste = $request->tipo_ajuste;
        $valorAjuste = (float)$request->valor_ajuste;
        $redondeo = $request->redondeo;
        $motivo = trim($request->motivo);
        $aplicarPresentaciones = (bool)$request->aplicar_presentaciones;
        $usuarioId = Auth::id();
        $ahora = now();

        $query = Producto::with(['presentaciones' => function ($pq) {
            $pq->where('activo', true);
        }])->whereNull('deleted_at')->where('precio_venta', '>', 0);

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

        $productos = $query->get();
        $totalActualizados = 0;

        DB::transaction(function () use ($productos, $tipoAjuste, $valorAjuste, $redondeo, $motivo, $aplicarPresentaciones, $usuarioId, $ahora, &$totalActualizados) {
            foreach ($productos as $prod) {
                $precioActual = (float)$prod->precio_venta;
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

                if ($redondeo === '0.05') {
                    $precioNuevo = round($precioNuevo / 0.05) * 0.05;
                } elseif ($redondeo === 'unidad') {
                    $precioNuevo = round($precioNuevo);
                } else {
                    $precioNuevo = round($precioNuevo, 2);
                }

                $precioNuevo = max(0.01, $precioNuevo);

                if (abs($precioNuevo - $precioActual) >= 0.01) {
                    // Cerrar precio base anterior
                    PrecioVenta::where('producto_id', $prod->id)
                        ->whereNull('presentacion_id')
                        ->whereNull('vigente_hasta')
                        ->update(['vigente_hasta' => $ahora]);

                    // Crear nuevo precio base
                    PrecioVenta::create([
                        'producto_id'     => $prod->id,
                        'presentacion_id' => null,
                        'precio'          => $precioNuevo,
                        'vigente_desde'   => $ahora,
                        'vigente_hasta'   => null,
                        'motivo'          => $motivo . " (Lote masivo)",
                        'user_id'         => $usuarioId,
                    ]);

                    $prod->update(['precio_venta' => $precioNuevo]);
                    $totalActualizados++;
                }

                // Ajustar presentaciones si se marcó la opción
                if ($aplicarPresentaciones && $prod->presentaciones->isNotEmpty()) {
                    foreach ($prod->presentaciones as $pres) {
                        $factor = max(1, (int)$pres->unidades_por_presentacion);
                        $nuevoPresPrecio = round($precioNuevo * $factor, 2);

                        // Cerrar precio anterior
                        PrecioVenta::where('producto_id', $prod->id)
                            ->where('presentacion_id', $pres->id)
                            ->whereNull('vigente_hasta')
                            ->update(['vigente_hasta' => $ahora]);

                        // Crear nuevo precio de presentación
                        PrecioVenta::create([
                            'producto_id'     => $prod->id,
                            'presentacion_id' => $pres->id,
                            'precio'          => $nuevoPresPrecio,
                            'vigente_desde'   => $ahora,
                            'vigente_hasta'   => null,
                            'motivo'          => $motivo . " (Lote masivo x{$factor})",
                            'user_id'         => $usuarioId,
                        ]);

                        $pres->update(['precio_venta' => $nuevoPresPrecio]);
                    }
                }
            }

            if (class_exists(AuditLog::class)) {
                AuditLog::log('precios', 'actualizacion_masiva', "Actualización masiva de precios aplicada a {$totalActualizados} productos", [
                    'motivo' => $motivo,
                    'total_productos' => $totalActualizados,
                    'tipo_ajuste' => $tipoAjuste,
                    'valor_ajuste' => $valorAjuste,
                ]);
            }
        });

        return redirect()->route('precios.index')
            ->with('success', "Actualización masiva completada: Se actualizaron los precios de {$totalActualizados} productos con éxito.");
    }

    /**
     * Historial General de Cambios de Precio (Auditoría)
     */
    public function historialGeneral(Request $request)
    {
        $query = PrecioVenta::with([
            'producto.categoria:id,nombre',
            'producto.laboratorio:id,nombre',
            'presentacion',
            'usuario:id,name'
        ]);

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->whereHas('producto', function ($pq) use ($q) {
                $pq->where('nombre', 'like', "%{$q}%")
                   ->orWhere('codigo_barra', 'like', "%{$q}%")
                   ->orWhere('principio_activo', 'like', "%{$q}%");
            });
        }

        if ($request->filled('desde')) {
            $query->whereDate('vigente_desde', '>=', $request->desde);
        }

        if ($request->filled('hasta')) {
            $query->whereDate('vigente_desde', '<=', $request->hasta);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $historial = $query->orderByDesc('vigente_desde')->orderByDesc('id')->paginate(perPage(25))->withQueryString();
        $usuarios = \App\Models\User::orderBy('name')->get(['id', 'name']);

        return view('precios.historial', compact('historial', 'usuarios'));
    }

    /**
     * Exportar Precios de Venta Vigentes a CSV
     */
    public function exportar(Request $request)
    {
        $productos = Producto::with(['categoria:id,nombre', 'laboratorio:id,nombre', 'presentacionesActivas'])
            ->whereNull('deleted_at')
            ->orderBy('nombre', 'asc')
            ->get();

        $filename = 'Precios_Venta_' . now()->format('Ymd_His') . '.xls';

        $content = view('precios.excel.exportar', compact('productos'))->render();

        return response($content, 200, [
            'Content-Type'        => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ]);
    }

    /**
     * API Autocompletado / Búsqueda Asíncrona (AJAX) para Categoría, Laboratorio, Proveedor y Medicamento
     */
    public function apiBuscar(Request $request)
    {
        $tipo = $request->get('tipo', 'producto'); // 'producto', 'categoria', 'laboratorio', 'proveedor'
        $q = trim($request->get('q', ''));

        if (strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $results = [];

        if ($tipo === 'categoria') {
            $items = Categoria::activas()
                ->where('nombre', 'like', "%{$q}%")
                ->limit(10)
                ->get(['id', 'nombre']);
            foreach ($items as $item) {
                $results[] = ['id' => $item->id, 'text' => $item->nombre];
            }
        } elseif ($tipo === 'laboratorio') {
            $items = Laboratorio::activos()
                ->where('nombre', 'like', "%{$q}%")
                ->limit(10)
                ->get(['id', 'nombre']);
            foreach ($items as $item) {
                $results[] = ['id' => $item->id, 'text' => $item->nombre];
            }
        } elseif ($tipo === 'proveedor') {
            $items = Proveedor::activos()
                ->where('nombre', 'like', "%{$q}%")
                ->limit(10)
                ->get(['id', 'nombre']);
            foreach ($items as $item) {
                $results[] = ['id' => $item->id, 'text' => $item->nombre];
            }
        } else {
            $items = Producto::whereNull('deleted_at')
                ->where(function ($sub) use ($q) {
                    $sub->where('nombre', 'like', "%{$q}%")
                        ->orWhere('codigo_barra', 'like', "%{$q}%")
                        ->orWhere('principio_activo', 'like', "%{$q}%");
                })
                ->limit(10)
                ->get(['id', 'nombre', 'codigo_barra', 'precio_venta', 'precio_compra']);
            foreach ($items as $item) {
                $results[] = [
                    'id' => $item->id,
                    'text' => $item->nombre . ($item->codigo_barra ? " ({$item->codigo_barra})" : ""),
                    'precio_venta' => (float)$item->precio_venta,
                    'precio_compra' => (float)$item->precio_compra,
                ];
            }
        }

        return response()->json(['results' => $results]);
    }
}
