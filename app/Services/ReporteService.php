<?php

namespace App\Services;

use App\Models\Venta;
use App\Models\Compra;
use App\Models\Producto;
use App\Models\Lote;
use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Models\DetalleVenta;
use App\Models\Cliente;
use App\Models\Receta;
use App\Models\User;
use App\Models\SesionCaja;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ReporteService
{
    protected InventarioService $inventarioService;

    public function __construct(InventarioService $inventarioService)
    {
        $this->inventarioService = $inventarioService;
    }

    /**
     * Resumen de KPIs ejecutivos para la vista principal del centro de reportes
     */
    public function getDashboardKpis(): array
    {
        $inicioMes = now()->startOfMonth()->toDateTimeString();
        $finMes = now()->endOfMonth()->toDateTimeString();
        $hoyInicio = today()->startOfDay()->toDateTimeString();
        $hoyFin = today()->endOfDay()->toDateTimeString();

        $ventasMes = (float) Venta::whereBetween('fecha', [$inicioMes, $finMes])
            ->completadas()
            ->sum('total');

        $cantidadVentasMes = (int) Venta::whereBetween('fecha', [$inicioMes, $finMes])
            ->completadas()
            ->count();

        $ventasHoy = (float) Venta::whereBetween('fecha', [$hoyInicio, $hoyFin])
            ->completadas()
            ->sum('total');

        $comprasMes = (float) Compra::whereBetween('fecha', [$inicioMes, $finMes])
            ->recibidas()
            ->sum('total');

        $valorizacion = $this->inventarioService->valorizacionInventario();

        $topProductosMes = DetalleVenta::join('productos', 'detalle_venta.producto_id', '=', 'productos.id')
            ->join('ventas', 'detalle_venta.venta_id', '=', 'ventas.id')
            ->where('ventas.estado', 'completada')
            ->whereBetween('ventas.fecha', [$inicioMes, $finMes])
            ->select(
                'productos.id',
                'productos.nombre',
                DB::raw('SUM(detalle_venta.cantidad_unidades_base) as total_unidades'),
                DB::raw('SUM(detalle_venta.subtotal) as total_monto')
            )
            ->groupBy('productos.id', 'productos.nombre')
            ->orderByDesc('total_unidades')
            ->take(5)
            ->get();

        $metodosMes = Venta::whereBetween('fecha', [$inicioMes, $finMes])
            ->completadas()
            ->select('metodo_pago', DB::raw('SUM(total) as total'), DB::raw('COUNT(id) as cantidad'))
            ->groupBy('metodo_pago')
            ->get();

        $today = now()->toDateString();
        $lotesVencidosCount = Lote::activos()->where('stock_actual', '>', 0)->where('fecha_vencimiento', '<=', $today)->count();
        $lotesCriticosCount = Lote::activos()->where('stock_actual', '>', 0)->whereBetween('fecha_vencimiento', [now()->addDay()->toDateString(), now()->addDays(30)->toDateString()])->count();
        $productosBajoStockCount = Producto::activos()->bajoStock()->count();

        return [
            'ventasMes'               => $ventasMes,
            'cantidadVentasMes'       => $cantidadVentasMes,
            'ventasHoy'               => $ventasHoy,
            'comprasMes'              => $comprasMes,
            'valorizacion'            => $valorizacion,
            'topProductosMes'         => $topProductosMes,
            'metodosMes'              => $metodosMes,
            'lotesVencidosCount'      => $lotesVencidosCount,
            'lotesCriticosCount'      => $lotesCriticosCount,
            'productosBajoStockCount' => $productosBajoStockCount,
        ];
    }

    /**
     * Construir consulta base para Reporte de Ventas con filtros aplicados
     */
    public function getQueryVentas(array $filters)
    {
        $fechaDesde = $filters['fecha_desde'] ?? now()->startOfMonth()->toDateString();
        $fechaHasta = $filters['fecha_hasta'] ?? now()->endOfMonth()->toDateString();
        $metodoPago = $filters['metodo_pago'] ?? null;
        $cajeroId = $filters['cajero_id'] ?? null;

        $query = Venta::with(['cliente:id,nombre,documento,telefono', 'usuario:id,name', 'detalles.producto:id,nombre'])
            ->completadas()
            ->whereBetween('fecha', ["{$fechaDesde} 00:00:00", "{$fechaHasta} 23:59:59"]);

        if (!empty($metodoPago)) {
            $query->where('metodo_pago', $metodoPago);
        }

        if (!empty($cajeroId)) {
            $query->where('user_id', $cajeroId);
        }

        return $query;
    }

    /**
     * Obtener estadísticas agregadas del Reporte de Ventas
     */
    public function getEstadisticasVentas($queryVentas, array $filters): array
    {
        $fechaDesde = $filters['fecha_desde'] ?? now()->startOfMonth()->toDateString();
        $fechaHasta = $filters['fecha_hasta'] ?? now()->endOfMonth()->toDateString();
        $metodoPago = $filters['metodo_pago'] ?? null;
        $cajeroId = $filters['cajero_id'] ?? null;

        $stats = (clone $queryVentas)->selectRaw('
            COUNT(id) as cantidad_ventas,
            COALESCE(SUM(total), 0) as total_vendido
        ')->first();

        $cantidadVentas = (int) ($stats->cantidad_ventas ?? 0);
        $totalVendido = (float) ($stats->total_vendido ?? 0);
        $ticketPromedio = $cantidadVentas > 0 ? round($totalVendido / $cantidadVentas, 2) : 0;

        $ventasPorMetodo = (clone $queryVentas)
            ->select('metodo_pago', DB::raw('SUM(total) as total'), DB::raw('COUNT(id) as cantidad'))
            ->groupBy('metodo_pago')
            ->get();

        $topProductos = DetalleVenta::join('productos', 'detalle_venta.producto_id', '=', 'productos.id')
            ->join('ventas', 'detalle_venta.venta_id', '=', 'ventas.id')
            ->where('ventas.estado', 'completada')
            ->whereBetween('ventas.fecha', ["{$fechaDesde} 00:00:00", "{$fechaHasta} 23:59:59"])
            ->when(!empty($metodoPago), fn($q) => $q->where('ventas.metodo_pago', $metodoPago))
            ->when(!empty($cajeroId), fn($q) => $q->where('ventas.user_id', $cajeroId))
            ->select(
                'productos.id',
                'productos.nombre',
                'productos.principio_activo',
                DB::raw('SUM(detalle_venta.cantidad_unidades_base) as total_unidades'),
                DB::raw('SUM(detalle_venta.subtotal) as total_ingreso')
            )
            ->groupBy('productos.id', 'productos.nombre', 'productos.principio_activo')
            ->orderByDesc('total_unidades')
            ->take(10)
            ->get();

        return [
            'totalVendido'    => $totalVendido,
            'cantidadVentas'  => $cantidadVentas,
            'ticketPromedio'  => $ticketPromedio,
            'ventasPorMetodo' => $ventasPorMetodo,
            'topProductos'    => $topProductos,
        ];
    }

    /**
     * Construir consulta base para Reporte de Inventario con filtros aplicados
     */
    public function getQueryInventario(array $filters)
    {
        $buscar = trim($filters['buscar'] ?? '');
        $categoriaId = $filters['categoria_id'] ?? null;
        $laboratorioId = $filters['laboratorio_id'] ?? null;
        $estadoVencimiento = $filters['estado_vencimiento'] ?? 'todos';
        $estadoStock = $filters['estado_stock'] ?? 'todos';
        $today = now()->toDateString();

        $query = Lote::with(['producto.categoria:id,nombre', 'producto.laboratorio:id,nombre', 'proveedor:id,nombre'])
            ->where('lotes.activo', true);

        if (!empty($buscar)) {
            $query->where(function ($q) use ($buscar) {
                $q->where('numero_lote', 'like', "%{$buscar}%")
                  ->orWhereHas('producto', function ($pq) use ($buscar) {
                      $pq->where('nombre', 'like', "%{$buscar}%")
                         ->orWhere('codigo_barra', 'like', "%{$buscar}%")
                         ->orWhere('principio_activo', 'like', "%{$buscar}%");
                  });
            });
        }

        if (!empty($categoriaId)) {
            $query->whereHas('producto', fn($q) => $q->where('categoria_id', $categoriaId));
        }

        if (!empty($laboratorioId)) {
            $query->whereHas('producto', fn($q) => $q->where('laboratorio_id', $laboratorioId));
        }

        if ($estadoVencimiento === 'vencidos') {
            $query->where('fecha_vencimiento', '<=', $today);
        } elseif ($estadoVencimiento === 'critico_30') {
            $query->whereBetween('fecha_vencimiento', [now()->toDateString(), now()->addDays(30)->toDateString()]);
        } elseif ($estadoVencimiento === 'alerta_60') {
            $query->whereBetween('fecha_vencimiento', [now()->addDays(31)->toDateString(), now()->addDays(60)->toDateString()]);
        } elseif ($estadoVencimiento === 'preventivo_90') {
            $query->whereBetween('fecha_vencimiento', [now()->addDays(61)->toDateString(), now()->addDays(90)->toDateString()]);
        } elseif ($estadoVencimiento === 'vigentes') {
            $query->where('fecha_vencimiento', '>', now()->addDays(90)->toDateString());
        }

        if ($estadoStock === 'agotado') {
            $query->where('stock_actual', 0);
        } elseif ($estadoStock === 'disponible') {
            $query->where('stock_actual', '>', 0);
        } elseif ($estadoStock === 'bajo_stock') {
            $query->whereHas('producto', fn($q) => $q->bajoStock());
        }

        return $query;
    }

    /**
     * Obtener estadísticas y semáforos de caducidad del inventario
     */
    public function getEstadisticasInventario($queryInventario): array
    {
        $today = now()->toDateString();
        $semVencidos = Lote::activos()->where('stock_actual', '>', 0)->where('fecha_vencimiento', '<=', $today)->count();
        $semCritico30 = Lote::activos()->where('stock_actual', '>', 0)->whereBetween('fecha_vencimiento', [now()->addDay()->toDateString(), now()->addDays(30)->toDateString()])->count();
        $semAlerta60 = Lote::activos()->where('stock_actual', '>', 0)->whereBetween('fecha_vencimiento', [now()->addDays(31)->toDateString(), now()->addDays(60)->toDateString()])->count();
        $semPreventivo90 = Lote::activos()->where('stock_actual', '>', 0)->whereBetween('fecha_vencimiento', [now()->addDays(61)->toDateString(), now()->addDays(90)->toDateString()])->count();
        $semVigentes = Lote::activos()->where('stock_actual', '>', 0)->where('fecha_vencimiento', '>', now()->addDays(90)->toDateString())->count();

        // Agregados calculados directamente en base de datos mediante join optimizado
        $agregados = (clone $queryInventario)
            ->join('productos', 'lotes.producto_id', '=', 'productos.id')
            ->selectRaw('
                COALESCE(SUM(lotes.stock_actual * lotes.precio_compra), 0) as total_costo,
                COALESCE(SUM(lotes.stock_actual * COALESCE(productos.precio_venta, 0)), 0) as total_venta,
                COALESCE(SUM(lotes.stock_actual), 0) as total_unidades
            ')
            ->first();

        $totalValorCosto = round((float) ($agregados->total_costo ?? 0), 2);
        $totalValorVenta = round((float) ($agregados->total_venta ?? 0), 2);
        $totalUnidadesStock = (int) ($agregados->total_unidades ?? 0);

        $margenProyectado = $totalValorCosto > 0 
            ? round((($totalValorVenta - $totalValorCosto) / $totalValorCosto) * 100, 1) 
            : 0;

        $valorizacion = $this->inventarioService->valorizacionInventario();
        $totalProductosBajoStock = Producto::activos()->bajoStock()->count();

        return [
            'semVencidos'             => $semVencidos,
            'semCritico30'            => $semCritico30,
            'semAlerta60'             => $semAlerta60,
            'semPreventivo90'         => $semPreventivo90,
            'semVigentes'             => $semVigentes,
            'totalValorCosto'         => $totalValorCosto,
            'totalValorVenta'         => $totalValorVenta,
            'totalUnidadesStock'      => $totalUnidadesStock,
            'margenProyectado'        => $margenProyectado,
            'valorizacion'            => $valorizacion,
            'totalProductosBajoStock' => $totalProductosBajoStock,
        ];
    }

    /**
     * Construir consulta base para Reporte de Compras
     */
    public function getQueryCompras(array $filters)
    {
        $fechaDesde = $filters['fecha_desde'] ?? now()->startOfMonth()->toDateString();
        $fechaHasta = $filters['fecha_hasta'] ?? now()->endOfMonth()->toDateString();
        $proveedorId = $filters['proveedor_id'] ?? null;
        $condicionPago = $filters['condicion_pago'] ?? null;

        $query = Compra::with(['proveedor:id,nombre,ruc,rif', 'usuario:id,name'])
            ->recibidas()
            ->whereBetween('fecha', ["{$fechaDesde} 00:00:00", "{$fechaHasta} 23:59:59"]);

        if (!empty($proveedorId)) {
            $query->where('proveedor_id', $proveedorId);
        }

        if (!empty($condicionPago)) {
            $query->where('condicion_pago', $condicionPago);
        }

        return $query;
    }

    /**
     * Estadísticas de Compras y Proveedores
     */
    public function getEstadisticasCompras($queryCompras): array
    {
        $stats = (clone $queryCompras)->selectRaw('
            COUNT(id) as cantidad_compras,
            COALESCE(SUM(total), 0) as total_comprado
        ')->first();

        $cantidadCompras = (int) ($stats->cantidad_compras ?? 0);
        $totalComprado = (float) ($stats->total_comprado ?? 0);
        $promedioCompra = $cantidadCompras > 0 ? round($totalComprado / $cantidadCompras, 2) : 0;

        $comprasPorProveedor = (clone $queryCompras)
            ->join('proveedores', 'compras.proveedor_id', '=', 'proveedores.id')
            ->select('proveedores.nombre', DB::raw('SUM(compras.total) as total'), DB::raw('COUNT(compras.id) as cantidad'))
            ->groupBy('proveedores.id', 'proveedores.nombre')
            ->orderByDesc('total')
            ->get();

        return [
            'totalComprado'       => $totalComprado,
            'cantidadCompras'     => $cantidadCompras,
            'promedioCompra'      => $promedioCompra,
            'comprasPorProveedor' => $comprasPorProveedor,
        ];
    }

    /**
     * Ranking de productos más vendidos en un rango de fechas
     */
    public function getRankingProductosMasVendidos(string $fechaDesde, string $fechaHasta, int $limit = 20): Collection
    {
        return DetalleVenta::join('productos', 'detalle_venta.producto_id', '=', 'productos.id')
            ->join('ventas', 'detalle_venta.venta_id', '=', 'ventas.id')
            ->where('ventas.estado', 'completada')
            ->whereBetween('ventas.fecha', ["{$fechaDesde} 00:00:00", "{$fechaHasta} 23:59:59"])
            ->select(
                'productos.id',
                'productos.nombre',
                'productos.principio_activo',
                DB::raw('SUM(detalle_venta.cantidad_unidades_base) as total_unidades_vendidas'),
                DB::raw('SUM(detalle_venta.subtotal) as total_ingresos')
            )
            ->groupBy('productos.id', 'productos.nombre', 'productos.principio_activo')
            ->orderByDesc('total_unidades_vendidas')
            ->take($limit)
            ->get();
    }

    /**
     * Ranking de Clientes con mayor facturación y frecuencia
     */
    public function getRankingClientes(string $fechaDesde, string $fechaHasta, int $limit = 20): Collection
    {
        return Cliente::whereHas('ventas', function ($q) use ($fechaDesde, $fechaHasta) {
                $q->where('estado', 'completada')
                  ->whereBetween('fecha', ["{$fechaDesde} 00:00:00", "{$fechaHasta} 23:59:59"]);
            })
            ->withCount([
                'ventas as total_ventas' => function ($q) use ($fechaDesde, $fechaHasta) {
                    $q->where('estado', 'completada')
                      ->whereBetween('fecha', ["{$fechaDesde} 00:00:00", "{$fechaHasta} 23:59:59"]);
                }
            ])
            ->withSum([
                'ventas as monto_total' => function ($q) use ($fechaDesde, $fechaHasta) {
                    $q->where('estado', 'completada')
                      ->whereBetween('fecha', ["{$fechaDesde} 00:00:00", "{$fechaHasta} 23:59:59"]);
                }
            ], 'total')
            ->orderByDesc('monto_total')
            ->take($limit)
            ->get();
    }

    /**
     * Consulta y desglose de Recetas Médicas
     */
    public function getReporteRecetasData(array $filters): array
    {
        $fechaDesde = $filters['fecha_desde'] ?? now()->startOfMonth()->toDateString();
        $fechaHasta = $filters['fecha_hasta'] ?? now()->endOfMonth()->toDateString();
        $estadoFiltro = $filters['estado'] ?? null;

        $query = Receta::with(['cliente:id,nombre,documento,telefono'])
            ->whereBetween('created_at', ["{$fechaDesde} 00:00:00", "{$fechaHasta} 23:59:59"]);

        if (!empty($estadoFiltro)) {
            $query->where('estado', $estadoFiltro);
        }

        $baseQuery = Receta::whereBetween('created_at', ["{$fechaDesde} 00:00:00", "{$fechaHasta} 23:59:59"]);
        $totalRecetas = (clone $baseQuery)->count();
        $procesadas   = (clone $baseQuery)->whereIn('estado', ['procesada', 'dispensada_total'])->count();
        $pendientes   = (clone $baseQuery)->whereIn('estado', ['pendiente', 'dispensada_parcial'])->count();
        $vencidas     = (clone $baseQuery)->where('estado', 'vencida')->count();
        $rechazadas   = (clone $baseQuery)->where('estado', 'rechazada')->count();

        return [
            'query'        => $query,
            'totalRecetas' => $totalRecetas,
            'procesadas'   => $procesadas,
            'pendientes'   => $pendientes,
            'vencidas'     => $vencidas,
            'rechazadas'   => $rechazadas,
        ];
    }

    /**
     * Consulta y desglose de Cajas y Turnos
     */
    public function getReporteCajasData(array $filters): array
    {
        $fechaDesde = $filters['fecha_desde'] ?? now()->startOfMonth()->toDateString();
        $fechaHasta = $filters['fecha_hasta'] ?? now()->endOfMonth()->toDateString();
        $cajaId = $filters['caja_id'] ?? null;
        $cajeroId = $filters['cajero_id'] ?? null;
        $estado = $filters['estado'] ?? null;

        $query = SesionCaja::with(['caja:id,nombre,codigo', 'usuario:id,name', 'usuarioCierre:id,name'])
            ->whereBetween('fecha_apertura', ["{$fechaDesde} 00:00:00", "{$fechaHasta} 23:59:59"]);

        if (!empty($cajaId)) {
            $query->where('caja_id', $cajaId);
        }

        if (!empty($cajeroId)) {
            $query->where('user_id', $cajeroId);
        }

        if (!empty($estado)) {
            $query->where('estado', $estado);
        }

        $baseQuery = clone $query;
        $stats = (clone $baseQuery)->selectRaw('
            COUNT(id) as total_sesiones,
            COALESCE(SUM(total_ventas), 0) as total_ventas_cajas,
            COALESCE(SUM(total_ventas_efectivo), 0) as total_ventas_efectivo,
            COALESCE(SUM(total_ventas_tarjeta), 0) as total_ventas_tarjeta,
            COALESCE(SUM(total_ventas_transferencia), 0) as total_ventas_transferencia,
            COALESCE(SUM(total_ingresos_manuales), 0) as total_ingresos_manuales,
            COALESCE(SUM(total_egresos_manuales), 0) as total_egresos_manuales
        ')->first();

        $diferenciaTotal = (float) (clone $baseQuery)->where('estado', 'cerrada')->sum('diferencia_efectivo');
        $sesionesConDiferencia = (int) (clone $baseQuery)->where('estado', 'cerrada')->where('diferencia_efectivo', '!=', 0)->count();

        return [
            'query'                    => $query,
            'totalSesiones'            => (int) ($stats->total_sesiones ?? 0),
            'totalVentasCajas'         => (float) ($stats->total_ventas_cajas ?? 0),
            'totalVentasEfectivo'      => (float) ($stats->total_ventas_efectivo ?? 0),
            'totalVentasTarjeta'       => (float) ($stats->total_ventas_tarjeta ?? 0),
            'totalVentasTransferencia' => (float) ($stats->total_ventas_transferencia ?? 0),
            'totalIngresosManuales'    => (float) ($stats->total_ingresos_manuales ?? 0),
            'totalEgresosManuales'     => (float) ($stats->total_egresos_manuales ?? 0),
            'diferenciaTotal'          => $diferenciaTotal,
            'sesionesConDiferencia'    => $sesionesConDiferencia,
        ];
    }

    /**
     * Consulta y desglose de Auditorías
     */
    public function getReporteAuditoriasData(array $filters): array
    {
        $fechaDesde = $filters['fecha_desde'] ?? now()->subDays(30)->toDateString();
        $fechaHasta = $filters['fecha_hasta'] ?? now()->toDateString();
        $modulo = $filters['modulo'] ?? null;
        $accion = $filters['accion'] ?? null;
        $userId = $filters['user_id'] ?? null;
        $buscar = trim($filters['buscar'] ?? '');

        $query = AuditLog::with('user:id,name,email')
            ->whereBetween('created_at', ["{$fechaDesde} 00:00:00", "{$fechaHasta} 23:59:59"]);

        if (!empty($modulo)) {
            $query->where('modulo', $modulo);
        }

        if (!empty($accion)) {
            $query->where('accion', $accion);
        }

        if (!empty($userId)) {
            $query->where('user_id', $userId);
        }

        if (!empty($buscar)) {
            $query->where(function ($q) use ($buscar) {
                $q->where('descripcion', 'like', "%{$buscar}%")
                  ->orWhere('ip', 'like', "%{$buscar}%")
                  ->orWhereHas('user', function ($uq) use ($buscar) {
                      $uq->where('name', 'like', "%{$buscar}%")
                         ->orWhere('email', 'like', "%{$buscar}%");
                  });
            });
        }

        $baseQuery = clone $query;
        $totalLogs = (clone $baseQuery)->count();
        $usuariosActivos = (clone $baseQuery)->distinct('user_id')->count('user_id');
        $modulosAuditados = (clone $baseQuery)->distinct('modulo')->count('modulo');
        $accionesCriticas = (clone $baseQuery)->whereIn(DB::raw('UPPER(accion)'), [
            'ELIMINAR', 'DELETE', 'ANULAR', 'AJUSTE', 'DESACTIVAR', 'UPDATE', 'EDITAR'
        ])->count();

        return [
            'query'            => $query,
            'totalLogs'        => $totalLogs,
            'usuariosActivos'  => $usuariosActivos,
            'modulosAuditados' => $modulosAuditados,
            'accionesCriticas' => $accionesCriticas,
        ];
    }
}
