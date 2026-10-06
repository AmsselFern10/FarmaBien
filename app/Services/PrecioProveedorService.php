<?php

namespace App\Services;

use App\Models\HistorialPrecio;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\PresentacionProducto;
use App\Models\AuditLog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class PrecioProveedorService
{
    /**
     * Obtener la comparativa estructurada de precios por proveedor y métricas de mercado.
     *
     * @param int $productoId
     * @return array
     */
    public function getComparativaProducto(int $productoId): array
    {
        $producto = Producto::with(['categoria', 'laboratorio', 'presentacionesActivas'])->find($productoId);
        if (!$producto) {
            return [
                'producto'             => null,
                'comparativa'          => collect(),
                'mejor_precio'         => null,
                'mayor_precio'         => null,
                'ahorro_maximo'        => 0.00,
                'precio_promedio'      => null,
                'proveedor_recomendado'=> null,
                'total_distribuidores' => 0,
            ];
        }

        $historiales = HistorialPrecio::with(['proveedor', 'presentacion', 'compra.usuario'])
            ->where('producto_id', $productoId)
            ->whereHas('proveedor', fn($q) => $q->where('activo', true))
            ->orderBy('fecha', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $porProveedor = [];

        foreach ($historiales as $h) {
            $provId = $h->proveedor_id;
            if (!isset($porProveedor[$provId])) {
                $porProveedor[$provId] = [
                    'proveedor'                 => $h->proveedor,
                    'ultimo_registro'           => $h,
                    'ultimo_precio_base'        => (float) $h->precio_unitario_base,
                    'ultimo_precio_compra'      => (float) $h->precio_compra,
                    'ultima_presentacion'       => $h->presentacion->nombre ?? ($h->tipo_presentacion ?: 'Unidad Base'),
                    'unidades_por_presentacion' => $h->presentacion->unidades_por_presentacion ?? $h->unidades_por_presentacion ?? 1,
                    'ultima_fecha'              => $h->fecha,
                    'mejor_precio_base'         => (float) $h->precio_unitario_base,
                    'mejor_registro'            => $h,
                    'total_operaciones'         => 0,
                    'tipo_origen'               => $h->tipo,
                ];
            }

            $porProveedor[$provId]['total_operaciones']++;

            if ((float) $h->precio_unitario_base < $porProveedor[$provId]['mejor_precio_base']) {
                $porProveedor[$provId]['mejor_precio_base'] = (float) $h->precio_unitario_base;
                $porProveedor[$provId]['mejor_registro'] = $h;
            }
        }

        $comparativa = collect($porProveedor)->sortBy('ultimo_precio_base')->values();

        $mejorPrecio = null;
        $mayorPrecio = null;
        $ahorroMaximo = 0.00;
        $precioPromedio = null;
        $proveedorRecomendado = null;

        if ($comparativa->isNotEmpty()) {
            $mejorPrecio = (float) $comparativa->min('mejor_precio_base');
            $mayorPrecio = (float) $comparativa->max('ultimo_precio_base');
            $proveedorRecomendado = $comparativa->sortBy('ultimo_precio_base')->first()['proveedor'] ?? null;
            $precioPromedio = round((float) $comparativa->avg('ultimo_precio_base'), 4);
            $ahorroMaximo = max(0, round($mayorPrecio - $mejorPrecio, 4));
        }

        return [
            'producto'              => $producto,
            'comparativa'           => $comparativa,
            'mejor_precio'          => $mejorPrecio,
            'mayor_precio'          => $mayorPrecio,
            'ahorro_maximo'         => $ahorroMaximo,
            'precio_promedio'       => $precioPromedio,
            'proveedor_recomendado' => $proveedorRecomendado,
            'total_distribuidores'  => $comparativa->count(),
        ];
    }

    /**
     * Obtener medicamentos bioequivalentes de otros laboratorios (mismo principio activo y concentración).
     *
     * @param Producto $producto
     * @return Collection
     */
    public function getEquivalentesFarmaceuticos(Producto $producto): Collection
    {
        $principio = trim($producto->principio_activo ?? '');
        if (empty($principio)) {
            return collect();
        }

        return Producto::with(['laboratorio:id,nombre', 'categoria:id,nombre', 'presentacionesActivas'])
            ->where('id', '!=', $producto->id)
            ->where('activo', true)
            ->where(function ($q) use ($principio, $producto) {
                $q->where('principio_activo', 'like', "%{$principio}%");
                if (!empty($producto->concentracion)) {
                    $q->where('concentracion', $producto->concentracion);
                }
            })
            ->orderBy('nombre')
            ->limit(10)
            ->get();
    }

    /**
     * Registrar una cotización comercial de un proveedor.
     *
     * @param array $data
     * @param int $userId
     * @return HistorialPrecio
     * @throws Exception
     */
    public function registrarCotizacion(array $data, int $userId): HistorialPrecio
    {
        return DB::transaction(function () use ($data, $userId) {
            $producto = Producto::findOrFail((int) $data['producto_id']);
            $proveedor = Proveedor::findOrFail((int) $data['proveedor_id']);

            if (!$producto->activo) {
                throw new Exception("El producto '{$producto->nombre}' se encuentra inactivo.");
            }
            if (!$proveedor->activo) {
                throw new Exception("El proveedor '{$proveedor->nombre}' se encuentra inactivo.");
            }

            $unidadesPorPresentacion = 1;
            $tipoPresentacion = 'Unidad Base';
            $presentacionId = !empty($data['presentacion_id']) ? (int) $data['presentacion_id'] : null;

            if ($presentacionId) {
                $pres = PresentacionProducto::where('producto_id', $producto->id)
                    ->where('id', $presentacionId)
                    ->firstOrFail();
                $unidadesPorPresentacion = max(1, (int) $pres->unidades_por_presentacion);
                $tipoPresentacion = $pres->nombre;
            }

            $precioCompra = round((float) $data['precio_compra'], 4);
            if ($precioCompra <= 0) {
                throw new Exception("El precio cotizado debe ser mayor a C$ 0.0000.");
            }

            $precioUnitarioBase = round($precioCompra / $unidadesPorPresentacion, 4);
            $fecha = !empty($data['fecha']) ? Carbon::parse($data['fecha']) : now();

            $registro = HistorialPrecio::create([
                'producto_id'               => $producto->id,
                'proveedor_id'              => $proveedor->id,
                'compra_id'                 => null,
                'presentacion_id'           => $presentacionId,
                'tipo_presentacion'         => $tipoPresentacion,
                'unidades_por_presentacion' => $unidadesPorPresentacion,
                'precio_compra'             => $precioCompra,
                'precio_unitario_base'      => $precioUnitarioBase,
                'tipo'                      => 'cotizacion',
                'fecha'                     => $fecha,
                'observaciones'             => !empty($data['observaciones']) ? trim($data['observaciones']) : 'Cotización de proveedor',
            ]);

            AuditLog::log('compras', 'cotizacion', "Cotización registrada para {$producto->nombre} por {$proveedor->nombre}: C$ {$precioCompra}", [
                'historial_id'         => $registro->id,
                'producto_id'          => $producto->id,
                'proveedor_id'         => $proveedor->id,
                'precio_compra'        => $precioCompra,
                'precio_unitario_base' => $precioUnitarioBase,
                'tipo_presentacion'    => $tipoPresentacion,
                'user_id'              => $userId,
            ]);

            Log::info("Cotización #{$registro->id} registrada para producto #{$producto->id} ({$producto->nombre})");

            return $registro->load(['producto', 'proveedor', 'presentacion']);
        });
    }

    /**
     * Obtener el costo de referencia único y consensuado para un producto en todo el sistema.
     *
     * @param Producto $producto
     * @return float
     */
    public function getCostoReferencia(Producto $producto): float
    {
        $ultimoRegistro = HistorialPrecio::where('producto_id', $producto->id)
            ->whereHas('proveedor', fn($q) => $q->where('activo', true))
            ->orderBy('fecha', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        if ($ultimoRegistro && (float) $ultimoRegistro->precio_unitario_base > 0) {
            return (float) $ultimoRegistro->precio_unitario_base;
        }

        if ((float) $producto->precio_compra > 0) {
            return (float) $producto->precio_compra;
        }

        return round((float) $producto->precio_venta * 0.70, 4);
    }

    /**
     * Obtener la mejor opción de proveedor y precio para un producto.
     *
     * @param Producto $producto
     * @return array|null
     */
    public function getMejorProveedor(Producto $producto): ?array
    {
        $mejorRegistro = HistorialPrecio::with('proveedor')
            ->where('producto_id', $producto->id)
            ->whereHas('proveedor', fn($q) => $q->where('activo', true))
            ->orderBy('precio_unitario_base', 'asc')
            ->orderBy('fecha', 'desc')
            ->first();

        if ($mejorRegistro && $mejorRegistro->proveedor) {
            return [
                'proveedor'            => $mejorRegistro->proveedor,
                'precio_unitario_base' => (float) $mejorRegistro->precio_unitario_base,
                'precio_compra'        => (float) $mejorRegistro->precio_compra,
                'tipo_presentacion'    => $mejorRegistro->tipo_presentacion,
                'tipo'                 => $mejorRegistro->tipo,
                'fecha'                => $mejorRegistro->fecha,
            ];
        }

        return null;
    }
}
