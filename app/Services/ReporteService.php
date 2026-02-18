<?php

namespace App\Services;

use App\Models\Compra;
use App\Models\Lote;
use App\Models\MovimientoInventario;
use App\Models\Venta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReporteService
{
    /**
     * Agrupa una colección y calcula subtotales.
     *
     * @return array<int, array{key:string,label:string,rows:array,subtotal:array{count:int,bruto:float,descuento:float,total:float,recibido?:float,vuelto?:float,neto_caja?:float,unidades?:int,ingresos?:float}}>
     */
    private function buildGroups(Collection $rows, callable $keyFn, callable $labelFn, callable $subtotalFn): array
    {
        $out = [];
        $groups = $rows->groupBy(fn ($r) => (string) $keyFn($r));

        foreach ($groups as $key => $bucket) {
            $out[] = [
                'key' => (string) $key,
                'label' => (string) $labelFn($bucket->first(), (string) $key),
                'rows' => $bucket->values()->all(),
                'subtotal' => $subtotalFn($bucket),
            ];
        }

        return $out;
    }

    /**
     * Builder base de ventas completadas con filtros comunes.
     *
     * @param array $filtros
     */
    private function ventasBaseQuery(array $filtros): Builder
    {
        $inicio = $filtros['inicio'];
        $fin = $filtros['fin'];

        $q = Venta::query()
            ->with(['cliente', 'usuario'])
            ->where('estado', 'completada')
            ->whereBetween('fecha', [$inicio, $fin]);

        if (!empty($filtros['user_id'])) {
            $q->where('user_id', (int) $filtros['user_id']);
        }
        if (!empty($filtros['cliente_id'])) {
            $q->where('cliente_id', (int) $filtros['cliente_id']);
        }
        if (!empty($filtros['metodo_pago'])) {
            $q->where('metodo_pago', (string) $filtros['metodo_pago']);
        }

        // Filtros por producto/categoría (venta que contiene el producto/categoría)
        if (!empty($filtros['producto_id'])) {
            $pid = (int) $filtros['producto_id'];
            $q->whereHas('detalles', fn ($d) => $d->where('producto_id', $pid));
        }
        if (!empty($filtros['categoria_id'])) {
            $cid = (int) $filtros['categoria_id'];
            $q->whereHas('detalles.producto', fn ($p) => $p->where('categoria_id', $cid));
        }

        return $q;
    }

    /**
     * Reporte de ventas (completadas) con agregados.
     *
     * @param array{inicio:Carbon, fin:Carbon, user_id?:int|null, cliente_id?:int|null, metodo_pago?:string|null, producto_id?:int|null, categoria_id?:int|null} $filtros
     */
    public function ventas(array $filtros): array
    {
        $ventasQuery = $this->ventasBaseQuery($filtros);

        $orderDir = ($filtros['order_dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $groupBy = $filtros['group_by'] ?? null;
        $modo = $filtros['modo'] ?? 'detalle';

        $ventas = (clone $ventasQuery)
            ->orderBy('fecha', $orderDir)
            ->get();

        $totalVentas = (float) $ventas->sum('total');
        $cantidadVentas = (int) $ventas->count();
        $promedioVenta = $cantidadVentas > 0 ? round($totalVentas / $cantidadVentas, 2) : 0;

        // Ventas por día
        $ventasPorDia = (clone $ventasQuery)
            ->selectRaw('DATE(fecha) as fecha, COUNT(*) as cantidad, SUM(total) as total')
            ->groupBy(DB::raw('DATE(fecha)'))
            ->orderBy(DB::raw('DATE(fecha)'))
            ->get()
            ->map(fn ($row) => [
                'fecha' => Carbon::parse($row->fecha)->format('d/m/Y'),
                'cantidad' => (int) $row->cantidad,
                'total' => (float) $row->total,
            ]);

        // Ventas por usuario (cajero)
        $ventasPorUsuario = (clone $ventasQuery)
            ->join('users', 'ventas.user_id', '=', 'users.id')
            ->selectRaw('ventas.user_id as user_id, users.name as usuario, COUNT(*) as cantidad, SUM(ventas.total) as total')
            ->groupBy('ventas.user_id', 'users.name')
            ->orderByDesc(DB::raw('SUM(ventas.total)'))
            ->get()
            ->map(fn ($row) => [
                'usuario' => (string) $row->usuario,
                'cantidad' => (int) $row->cantidad,
                'total' => (float) $row->total,
            ]);

        // Ventas por método de pago
        $ventasPorMetodoPago = (clone $ventasQuery)
            ->selectRaw('metodo_pago as metodo, COUNT(*) as cantidad, SUM(total) as total')
            ->groupBy('metodo_pago')
            ->orderByDesc(DB::raw('SUM(total)'))
            ->get()
            ->map(fn ($row) => [
                'metodo' => (string) ($row->metodo ?? '—'),
                'cantidad' => (int) $row->cantidad,
                'total' => (float) $row->total,
            ]);

        // Ventas por cliente
        $ventasPorCliente = (clone $ventasQuery)
            ->leftJoin('clientes', 'ventas.cliente_id', '=', 'clientes.id')
            ->selectRaw('ventas.cliente_id as cliente_id, COALESCE(clientes.nombre, "Mostrador") as cliente, COUNT(*) as cantidad, SUM(ventas.total) as total')
            ->groupBy('ventas.cliente_id', 'clientes.nombre')
            ->orderByDesc(DB::raw('SUM(ventas.total)'))
            ->get()
            ->map(fn ($row) => [
                'cliente' => (string) $row->cliente,
                'cantidad' => (int) $row->cantidad,
                'total' => (float) $row->total,
            ]);

        // Ventas por producto/categoría (unidades base)
        $ventasPorProducto = $this->ventasProductos($filtros, 50);
        $ventasPorCategoria = $this->ventasCategorias($filtros, 50);

        // =====================
        // Agrupación con subtotales + modo ejecutivo
        // =====================
        $grouped = null;
        $resumen = null;

        if ($groupBy) {
            if ($modo === 'resumen') {
                // Resumen por entidad (sin detalle de facturas)
                if ($groupBy === 'cajero') {
                    $resumen = $ventasPorUsuario;
                } elseif ($groupBy === 'cliente') {
                    $resumen = $ventasPorCliente;
                } elseif ($groupBy === 'producto') {
                    $resumen = $ventasPorProducto;
                } elseif ($groupBy === 'categoria') {
                    $resumen = $ventasPorCategoria;
                }
            } else {
                // Detalle agrupado con subtotales
                if (in_array($groupBy, ['cajero', 'cliente'], true)) {
                    $grouped = $this->buildGroups(
                        $ventas,
                        fn ($v) => $groupBy === 'cajero'
                            ? ($v->usuario?->id ?? '0')
                            : ($v->cliente_id ? (string) $v->cliente_id : '0'),
                        fn ($first, $key) => $groupBy === 'cajero'
                            ? (($first->usuario?->name) ?: '—')
                            : (($first->cliente?->nombre) ?: 'Cliente de mostrador'),
                        function (Collection $bucket) {
                            return [
                                'count' => (int) $bucket->count(),
                                'bruto' => (float) $bucket->sum('subtotal_bruto'),
                                'descuento' => (float) $bucket->sum('descuento_monto_total'),
                                'total' => (float) $bucket->sum('total'),
                                'recibido' => (float) $bucket->sum('monto_recibido'),
                                'vuelto' => (float) $bucket->sum('cambio'),
                                'neto_caja' => (float) ($bucket->sum('monto_recibido') - $bucket->sum('cambio')),
                            ];
                        }
                    );
                } elseif (in_array($groupBy, ['producto', 'categoria'], true)) {
                    // Para producto/categoría, el detalle se presenta por líneas (detalle_venta)
                    $lineas = $this->ventasDetalleLineas($filtros, $groupBy);
                    $grouped = $this->buildGroups(
                        $lineas,
                        fn ($r) => $groupBy === 'producto' ? ($r['producto'] ?? '—') : ($r['categoria'] ?? '—'),
                        fn ($first, $key) => (string) $key,
                        function (Collection $bucket) {
                            return [
                                'count' => (int) $bucket->count(),
                                'unidades' => (int) $bucket->sum('unidades'),
                                'ingresos' => (float) $bucket->sum('ingresos'),
                                'total' => (float) $bucket->sum('ingresos'),
                                'bruto' => (float) 0,
                                'descuento' => (float) 0,
                            ];
                        }
                    );
                }
            }
        }

        return [
            'ventas' => $ventas,
            'totalVentas' => $totalVentas,
            'cantidadVentas' => $cantidadVentas,
            'promedioVenta' => $promedioVenta,
            'ventasPorDia' => $ventasPorDia,
            'ventasPorUsuario' => $ventasPorUsuario,
            'ventasPorMetodoPago' => $ventasPorMetodoPago,
            'ventasPorCliente' => $ventasPorCliente,
            'ventasPorProducto' => $ventasPorProducto,
            'ventasPorCategoria' => $ventasPorCategoria,

            // Nuevos: agrupación / modo / orden
            'modo' => $modo,
            'group_by' => $groupBy,
            'order_dir' => $orderDir,
            'grouped' => $grouped,
            'resumen' => $resumen,
        ];
    }

    /**
     * Detalle de ventas por líneas (detalle_venta) para reportes agrupados por producto/categoría.
     * Cada fila representa una línea de factura.
     *
     * @return \Illuminate\Support\Collection<int, array>
     */
    private function ventasDetalleLineas(array $filtros, string $groupBy)
    {
        $inicio = $filtros['inicio'];
        $fin = $filtros['fin'];
        $orderDir = ($filtros['order_dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        $q = DB::table('detalle_venta as dv')
            ->join('ventas as v', 'dv.venta_id', '=', 'v.id')
            ->leftJoin('clientes as cl', 'v.cliente_id', '=', 'cl.id')
            ->join('users as u', 'v.user_id', '=', 'u.id')
            ->join('productos as p', 'dv.producto_id', '=', 'p.id')
            ->leftJoin('categorias as c', 'p.categoria_id', '=', 'c.id')
            ->where('v.estado', 'completada')
            ->whereBetween('v.fecha', [$inicio, $fin]);

        if (!empty($filtros['user_id'])) {
            $q->where('v.user_id', (int) $filtros['user_id']);
        }
        if (!empty($filtros['cliente_id'])) {
            $q->where('v.cliente_id', (int) $filtros['cliente_id']);
        }
        if (!empty($filtros['metodo_pago'])) {
            $q->where('v.metodo_pago', (string) $filtros['metodo_pago']);
        }
        if (!empty($filtros['producto_id'])) {
            $q->where('dv.producto_id', (int) $filtros['producto_id']);
        }
        if (!empty($filtros['categoria_id'])) {
            $q->where('p.categoria_id', (int) $filtros['categoria_id']);
        }

        return $q
            ->selectRaw('v.id as venta_id, v.fecha as fecha, COALESCE(cl.nombre, "Mostrador") as cliente, u.name as cajero')
            ->selectRaw('p.nombre as producto, COALESCE(c.nombre, "Sin categoría") as categoria')
            ->selectRaw('dv.cantidad_unidades_base as unidades, dv.subtotal as ingresos')
            ->orderBy('v.fecha', $orderDir)
            ->get()
            ->map(fn ($r) => [
                'venta_id' => (int) $r->venta_id,
                'fecha' => Carbon::parse($r->fecha)->format('d/m/Y H:i'),
                'cliente' => (string) $r->cliente,
                'cajero' => (string) $r->cajero,
                'producto' => (string) $r->producto,
                'categoria' => (string) $r->categoria,
                'unidades' => (int) $r->unidades,
                'ingresos' => (float) $r->ingresos,
            ]);
    }

    /**
     * Resumen de ventas por producto (unidades base + ingresos).
     */
    public function ventasProductos(array $filtros, int $limite = 50): Collection
    {
        $inicio = $filtros['inicio'];
        $fin = $filtros['fin'];

        $q = DB::table('detalle_venta as dv')
            ->join('ventas as v', 'dv.venta_id', '=', 'v.id')
            ->join('productos as p', 'dv.producto_id', '=', 'p.id')
            ->leftJoin('categorias as c', 'p.categoria_id', '=', 'c.id')
            ->where('v.estado', 'completada')
            ->whereBetween('v.fecha', [$inicio, $fin]);

        if (!empty($filtros['user_id'])) {
            $q->where('v.user_id', (int) $filtros['user_id']);
        }
        if (!empty($filtros['cliente_id'])) {
            $q->where('v.cliente_id', (int) $filtros['cliente_id']);
        }
        if (!empty($filtros['metodo_pago'])) {
            $q->where('v.metodo_pago', (string) $filtros['metodo_pago']);
        }
        if (!empty($filtros['producto_id'])) {
            $q->where('dv.producto_id', (int) $filtros['producto_id']);
        }
        if (!empty($filtros['categoria_id'])) {
            $q->where('p.categoria_id', (int) $filtros['categoria_id']);
        }

        return $q
            ->selectRaw('p.id, p.nombre as producto, COALESCE(c.nombre, "Sin categoría") as categoria')
            ->selectRaw('SUM(dv.cantidad_unidades_base) as unidades')
            ->selectRaw('SUM(dv.subtotal) as ingresos')
            ->selectRaw('COUNT(DISTINCT dv.venta_id) as ventas')
            ->groupBy('p.id', 'p.nombre', 'c.nombre')
            ->orderByDesc('unidades')
            ->limit($limite)
            ->get()
            ->map(fn ($row) => [
                'producto' => (string) $row->producto,
                'categoria' => (string) $row->categoria,
                'unidades' => (int) $row->unidades,
                'ingresos' => (float) $row->ingresos,
                'ventas' => (int) $row->ventas,
            ]);
    }

    /**
     * Resumen de ventas por categoría.
     */
    public function ventasCategorias(array $filtros, int $limite = 50): Collection
    {
        $inicio = $filtros['inicio'];
        $fin = $filtros['fin'];

        $q = DB::table('detalle_venta as dv')
            ->join('ventas as v', 'dv.venta_id', '=', 'v.id')
            ->join('productos as p', 'dv.producto_id', '=', 'p.id')
            ->leftJoin('categorias as c', 'p.categoria_id', '=', 'c.id')
            ->where('v.estado', 'completada')
            ->whereBetween('v.fecha', [$inicio, $fin]);

        if (!empty($filtros['user_id'])) {
            $q->where('v.user_id', (int) $filtros['user_id']);
        }
        if (!empty($filtros['cliente_id'])) {
            $q->where('v.cliente_id', (int) $filtros['cliente_id']);
        }
        if (!empty($filtros['metodo_pago'])) {
            $q->where('v.metodo_pago', (string) $filtros['metodo_pago']);
        }
        if (!empty($filtros['producto_id'])) {
            $q->where('dv.producto_id', (int) $filtros['producto_id']);
        }
        if (!empty($filtros['categoria_id'])) {
            $q->where('p.categoria_id', (int) $filtros['categoria_id']);
        }

        return $q
            ->selectRaw('COALESCE(c.nombre, "Sin categoría") as categoria')
            ->selectRaw('SUM(dv.cantidad_unidades_base) as unidades')
            ->selectRaw('SUM(dv.subtotal) as ingresos')
            ->selectRaw('COUNT(DISTINCT dv.venta_id) as ventas')
            ->groupBy('c.nombre')
            ->orderByDesc('ingresos')
            ->limit($limite)
            ->get()
            ->map(fn ($row) => [
                'categoria' => (string) $row->categoria,
                'unidades' => (int) $row->unidades,
                'ingresos' => (float) $row->ingresos,
                'ventas' => (int) $row->ventas,
            ]);
    }

    /**
     * Filas planas para exportar ventas (CSV/Excel). Cada fila = 1 venta.
     */
    public function ventasExportRows(array $filtros): array
    {
        // Si el reporte está en modo agrupado/resumen, exportamos con subtotales.
        if (!empty($filtros['group_by']) || (($filtros['modo'] ?? 'detalle') === 'resumen')) {
            return $this->ventasExportGroupedRows($filtros);
        }

        $orderDir = ($filtros['order_dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        $ventas = (clone $this->ventasBaseQuery($filtros))
            ->orderBy('fecha', $orderDir)
            ->get();

        return $ventas->map(function (Venta $v) {
            return [
                'ID' => $v->id,
                'Fecha' => optional($v->fecha)->format('Y-m-d H:i:s'),
                'Cliente' => $v->cliente?->nombre ?? 'Mostrador',
                'Cajero' => $v->usuario?->name ?? '—',
                'Método Pago' => $v->metodo_pago ?? '—',
                'Subtotal' => (float) $v->subtotal_bruto,
                'Descuento %' => (float) $v->descuento_porcentaje,
                'Descuento $' => (float) $v->descuento_monto_total,
                'Total' => (float) $v->total,
            ];
        })->toArray();
    }

    /**
     * Export con agrupación (incluye filas de SUBTOTAL) o modo resumen.
     */
    public function ventasExportGroupedRows(array $filtros): array
    {
        $payload = $this->ventas($filtros);
        $groupBy = $payload['group_by'] ?? null;
        $modo = $payload['modo'] ?? 'detalle';

        // Resumen ejecutivo
        if ($modo === 'resumen' && $groupBy) {
            $rows = [];
            foreach (($payload['resumen'] ?? []) as $r) {
                if ($groupBy === 'producto') {
                    $rows[] = [
                        'Producto' => $r['producto'],
                        'Categoría' => $r['categoria'],
                        'Unidades' => $r['unidades'],
                        'Ingresos' => $r['ingresos'],
                        'N° Ventas' => $r['ventas'],
                    ];
                } elseif ($groupBy === 'categoria') {
                    $rows[] = [
                        'Categoría' => $r['categoria'],
                        'Unidades' => $r['unidades'],
                        'Ingresos' => $r['ingresos'],
                        'N° Ventas' => $r['ventas'],
                    ];
                } elseif ($groupBy === 'cajero') {
                    $rows[] = [
                        'Cajero' => $r['usuario'],
                        'Operaciones' => $r['cantidad'],
                        'Total Ventas' => $r['total'],
                    ];
                } elseif ($groupBy === 'cliente') {
                    $rows[] = [
                        'Cliente' => $r['cliente'],
                        'Operaciones' => $r['cantidad'],
                        'Total Ventas' => $r['total'],
                    ];
                }
            }
            return $rows;
        }

        // Detalle agrupado + subtotales
        $rows = [];
        foreach (($payload['grouped'] ?? []) as $g) {
            $rows[] = ['GRUPO' => $g['label']];

            // Ventas por cajero/cliente: filas tipo factura
            if (in_array($groupBy, ['cajero', 'cliente'], true)) {
                foreach ($g['rows'] as $v) {
                    $rows[] = [
                        'ID' => $v->id,
                        'Fecha' => optional($v->fecha)->format('Y-m-d H:i:s'),
                        'Cliente' => $v->cliente?->nombre ?? 'Mostrador',
                        'Cajero' => $v->usuario?->name ?? '—',
                        'Método Pago' => $v->metodo_pago ?? '—',
                        'Subtotal' => (float) $v->subtotal_bruto,
                        'Descuento $' => (float) $v->descuento_monto_total,
                        'Total' => (float) $v->total,
                    ];
                }
                $rows[] = [
                    'ID' => 'SUBTOTAL',
                    'Fecha' => '',
                    'Cliente' => '',
                    'Cajero' => '',
                    'Método Pago' => '',
                    'Subtotal' => (float) ($g['subtotal']['bruto'] ?? 0),
                    'Descuento $' => (float) ($g['subtotal']['descuento'] ?? 0),
                    'Total' => (float) ($g['subtotal']['total'] ?? 0),
                ];
            } else {
                // Producto/categoría: filas por línea
                foreach ($g['rows'] as $r) {
                    $rows[] = [
                        'Venta ID' => $r['venta_id'],
                        'Fecha' => $r['fecha'],
                        'Cliente' => $r['cliente'],
                        'Cajero' => $r['cajero'],
                        'Producto' => $r['producto'],
                        'Categoría' => $r['categoria'],
                        'Unidades' => $r['unidades'],
                        'Ingresos' => $r['ingresos'],
                    ];
                }
                $rows[] = [
                    'Venta ID' => 'SUBTOTAL',
                    'Fecha' => '',
                    'Cliente' => '',
                    'Cajero' => '',
                    'Producto' => '',
                    'Categoría' => '',
                    'Unidades' => (int) ($g['subtotal']['unidades'] ?? 0),
                    'Ingresos' => (float) ($g['subtotal']['ingresos'] ?? 0),
                ];
            }

            $rows[] = []; // separador
        }

        return $rows;
    }

    /**
     * Reporte de compras (recibidas) con agregados.
     *
     * @param array{inicio:Carbon, fin:Carbon, user_id?:int|null, proveedor_id?:int|null, producto_id?:int|null, categoria_id?:int|null} $filtros
     */
    public function compras(array $filtros): array
    {
        $inicio = $filtros['inicio'];
        $fin = $filtros['fin'];

        $comprasQuery = Compra::query()
            ->with(['proveedor', 'usuario', 'detalles'])
            ->where('estado', 'recibida')
            ->whereBetween('fecha', [$inicio, $fin]);

        if (!empty($filtros['user_id'])) {
            $comprasQuery->where('user_id', (int) $filtros['user_id']);
        }
        if (!empty($filtros['proveedor_id'])) {
            $comprasQuery->where('proveedor_id', (int) $filtros['proveedor_id']);
        }

        if (!empty($filtros['producto_id'])) {
            $pid = (int) $filtros['producto_id'];
            $comprasQuery->whereHas('detalles', fn ($d) => $d->where('producto_id', $pid));
        }
        if (!empty($filtros['categoria_id'])) {
            $cid = (int) $filtros['categoria_id'];
            $comprasQuery->whereHas('detalles.producto', fn ($p) => $p->where('categoria_id', $cid));
        }

        $orderDir = ($filtros['order_dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $groupBy = $filtros['group_by'] ?? null;
        $modo = $filtros['modo'] ?? 'detalle';

        $compras = (clone $comprasQuery)
            ->orderBy('fecha', $orderDir)
            ->get();

        $totalCompras = (float) $compras->sum('total');
        $cantidadCompras = (int) $compras->count();
        $promedioCompra = $cantidadCompras > 0 ? round($totalCompras / $cantidadCompras, 2) : 0;

        // Compras por proveedor
        $comprasPorProveedor = $compras
            ->groupBy('proveedor_id')
            ->map(function (Collection $comprasProveedor) {
                $first = $comprasProveedor->first();

                return [
                    'proveedor' => (string) ($first->proveedor?->nombre ?? '—'),
                    'cantidad' => (int) $comprasProveedor->count(),
                    'total' => (float) $comprasProveedor->sum('total'),
                ];
            })
            ->sortByDesc('total')
            ->values();

        // Total de productos comprados (unidades base)
        $totalProductosComprados = (int) $compras
            ->flatMap(fn ($c) => $c->detalles)
            ->sum(fn ($d) => (int) ($d->cantidad_unidades_base ?? $d->cantidad ?? 0));

        $comprasPorProducto = $this->comprasProductos($filtros, 50);
        $comprasPorCategoria = $this->comprasCategorias($filtros, 50);

        $grouped = null;
        $resumen = null;

        if ($groupBy) {
            if ($modo === 'resumen') {
                if ($groupBy === 'proveedor') {
                    $resumen = $comprasPorProveedor;
                } elseif ($groupBy === 'usuario') {
                    // Resumen por usuario (cajero/operador)
                    $resumen = $compras->groupBy('user_id')->map(function (Collection $b) {
                        $first = $b->first();
                        return [
                            'usuario' => (string) ($first->usuario?->name ?? '—'),
                            'cantidad' => (int) $b->count(),
                            'total' => (float) $b->sum('total'),
                        ];
                    })->sortByDesc('total')->values();
                } elseif ($groupBy === 'producto') {
                    $resumen = $comprasPorProducto;
                } elseif ($groupBy === 'categoria') {
                    $resumen = $comprasPorCategoria;
                }
            } else {
                if (in_array($groupBy, ['proveedor', 'usuario'], true)) {
                    $grouped = $this->buildGroups(
                        $compras,
                        fn ($c) => $groupBy === 'proveedor' ? ($c->proveedor_id ? (string) $c->proveedor_id : '0') : ($c->user_id ? (string) $c->user_id : '0'),
                        fn ($first, $key) => $groupBy === 'proveedor'
                            ? (($first->proveedor?->nombre) ?: '—')
                            : (($first->usuario?->name) ?: '—'),
                        function (Collection $bucket) {
                            return [
                                'count' => (int) $bucket->count(),
                                'bruto' => (float) $bucket->sum('subtotal_bruto'),
                                'descuento' => (float) $bucket->sum('descuento_monto_total'),
                                'total' => (float) $bucket->sum('total'),
                            ];
                        }
                    );
                } elseif (in_array($groupBy, ['producto', 'categoria'], true)) {
                    $lineas = $this->comprasDetalleLineas($filtros);
                    $grouped = $this->buildGroups(
                        $lineas,
                        fn ($r) => $groupBy === 'producto' ? ($r['producto'] ?? '—') : ($r['categoria'] ?? '—'),
                        fn ($first, $key) => (string) $key,
                        function (Collection $bucket) {
                            return [
                                'count' => (int) $bucket->count(),
                                'unidades' => (int) $bucket->sum('unidades'),
                                'gasto' => (float) $bucket->sum('gasto'),
                                'total' => (float) $bucket->sum('gasto'),
                                'bruto' => (float) 0,
                                'descuento' => (float) 0,
                            ];
                        }
                    );
                }
            }
        }

        return [
            'compras' => $compras,
            'totalCompras' => $totalCompras,
            'cantidadCompras' => $cantidadCompras,
            'promedioCompra' => $promedioCompra,
            'comprasPorProveedor' => $comprasPorProveedor,
            'totalProductosComprados' => $totalProductosComprados,
            'comprasPorProducto' => $comprasPorProducto,
            'comprasPorCategoria' => $comprasPorCategoria,

            'modo' => $modo,
            'group_by' => $groupBy,
            'order_dir' => $orderDir,
            'grouped' => $grouped,
            'resumen' => $resumen,
        ];
    }

    /**
     * Detalle de compras por líneas (detalle_compra). Cada fila = 1 línea.
     */
    private function comprasDetalleLineas(array $filtros)
    {
        $inicio = $filtros['inicio'];
        $fin = $filtros['fin'];
        $orderDir = ($filtros['order_dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        $q = DB::table('detalle_compra as dc')
            ->join('compras as c', 'dc.compra_id', '=', 'c.id')
            ->leftJoin('proveedores as pr', 'c.proveedor_id', '=', 'pr.id')
            ->join('users as u', 'c.user_id', '=', 'u.id')
            ->join('productos as p', 'dc.producto_id', '=', 'p.id')
            ->leftJoin('categorias as cat', 'p.categoria_id', '=', 'cat.id')
            ->where('c.estado', 'recibida')
            ->whereBetween('c.fecha', [$inicio, $fin]);

        if (!empty($filtros['user_id'])) {
            $q->where('c.user_id', (int) $filtros['user_id']);
        }
        if (!empty($filtros['proveedor_id'])) {
            $q->where('c.proveedor_id', (int) $filtros['proveedor_id']);
        }
        if (!empty($filtros['producto_id'])) {
            $q->where('dc.producto_id', (int) $filtros['producto_id']);
        }
        if (!empty($filtros['categoria_id'])) {
            $q->where('p.categoria_id', (int) $filtros['categoria_id']);
        }

        return $q
            ->selectRaw('c.id as compra_id, c.fecha as fecha, COALESCE(pr.nombre, "—") as proveedor, u.name as usuario')
            ->selectRaw('p.nombre as producto, COALESCE(cat.nombre, "Sin categoría") as categoria')
            ->selectRaw('dc.cantidad_unidades_base as unidades, dc.subtotal as gasto')
            ->orderBy('c.fecha', $orderDir)
            ->get()
            ->map(fn ($r) => [
                'compra_id' => (int) $r->compra_id,
                'fecha' => Carbon::parse($r->fecha)->format('d/m/Y H:i'),
                'proveedor' => (string) $r->proveedor,
                'usuario' => (string) $r->usuario,
                'producto' => (string) $r->producto,
                'categoria' => (string) $r->categoria,
                'unidades' => (int) $r->unidades,
                'gasto' => (float) $r->gasto,
            ]);
    }

    public function comprasProductos(array $filtros, int $limite = 50): Collection
    {
        $inicio = $filtros['inicio'];
        $fin = $filtros['fin'];

        $q = DB::table('detalle_compra as dc')
            ->join('compras as c', 'dc.compra_id', '=', 'c.id')
            ->join('productos as p', 'dc.producto_id', '=', 'p.id')
            ->leftJoin('categorias as cat', 'p.categoria_id', '=', 'cat.id')
            ->where('c.estado', 'recibida')
            ->whereBetween('c.fecha', [$inicio, $fin]);

        if (!empty($filtros['user_id'])) {
            $q->where('c.user_id', (int) $filtros['user_id']);
        }
        if (!empty($filtros['proveedor_id'])) {
            $q->where('c.proveedor_id', (int) $filtros['proveedor_id']);
        }
        if (!empty($filtros['producto_id'])) {
            $q->where('dc.producto_id', (int) $filtros['producto_id']);
        }
        if (!empty($filtros['categoria_id'])) {
            $q->where('p.categoria_id', (int) $filtros['categoria_id']);
        }

        return $q
            ->selectRaw('p.id, p.nombre as producto, COALESCE(cat.nombre, "Sin categoría") as categoria')
            ->selectRaw('SUM(dc.cantidad_unidades_base) as unidades')
            ->selectRaw('SUM(dc.subtotal) as gasto')
            ->selectRaw('COUNT(DISTINCT dc.compra_id) as compras')
            ->groupBy('p.id', 'p.nombre', 'cat.nombre')
            ->orderByDesc('unidades')
            ->limit($limite)
            ->get()
            ->map(fn ($row) => [
                'producto' => (string) $row->producto,
                'categoria' => (string) $row->categoria,
                'unidades' => (int) $row->unidades,
                'gasto' => (float) $row->gasto,
                'compras' => (int) $row->compras,
            ]);
    }

    public function comprasCategorias(array $filtros, int $limite = 50): Collection
    {
        $inicio = $filtros['inicio'];
        $fin = $filtros['fin'];

        $q = DB::table('detalle_compra as dc')
            ->join('compras as c', 'dc.compra_id', '=', 'c.id')
            ->join('productos as p', 'dc.producto_id', '=', 'p.id')
            ->leftJoin('categorias as cat', 'p.categoria_id', '=', 'cat.id')
            ->where('c.estado', 'recibida')
            ->whereBetween('c.fecha', [$inicio, $fin]);

        if (!empty($filtros['user_id'])) {
            $q->where('c.user_id', (int) $filtros['user_id']);
        }
        if (!empty($filtros['proveedor_id'])) {
            $q->where('c.proveedor_id', (int) $filtros['proveedor_id']);
        }
        if (!empty($filtros['producto_id'])) {
            $q->where('dc.producto_id', (int) $filtros['producto_id']);
        }
        if (!empty($filtros['categoria_id'])) {
            $q->where('p.categoria_id', (int) $filtros['categoria_id']);
        }

        return $q
            ->selectRaw('COALESCE(cat.nombre, "Sin categoría") as categoria')
            ->selectRaw('SUM(dc.cantidad_unidades_base) as unidades')
            ->selectRaw('SUM(dc.subtotal) as gasto')
            ->selectRaw('COUNT(DISTINCT dc.compra_id) as compras')
            ->groupBy('cat.nombre')
            ->orderByDesc('gasto')
            ->limit($limite)
            ->get()
            ->map(fn ($row) => [
                'categoria' => (string) $row->categoria,
                'unidades' => (int) $row->unidades,
                'gasto' => (float) $row->gasto,
                'compras' => (int) $row->compras,
            ]);
    }

    /**
     * Filas planas para exportar compras (CSV/Excel). Cada fila = 1 compra.
     */
    public function comprasExportRows(array $filtros): array
    {
        if (!empty($filtros['group_by']) || (($filtros['modo'] ?? 'detalle') === 'resumen')) {
            return $this->comprasExportGroupedRows($filtros);
        }

        $inicio = $filtros['inicio'];
        $fin = $filtros['fin'];

        $q = Compra::query()
            ->with(['proveedor', 'usuario'])
            ->where('estado', 'recibida')
            ->whereBetween('fecha', [$inicio, $fin]);

        if (!empty($filtros['user_id'])) {
            $q->where('user_id', (int) $filtros['user_id']);
        }
        if (!empty($filtros['proveedor_id'])) {
            $q->where('proveedor_id', (int) $filtros['proveedor_id']);
        }
        if (!empty($filtros['producto_id'])) {
            $pid = (int) $filtros['producto_id'];
            $q->whereHas('detalles', fn ($d) => $d->where('producto_id', $pid));
        }
        if (!empty($filtros['categoria_id'])) {
            $cid = (int) $filtros['categoria_id'];
            $q->whereHas('detalles.producto', fn ($p) => $p->where('categoria_id', $cid));
        }

        $orderDir = ($filtros['order_dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $compras = $q->orderBy('fecha', $orderDir)->get();

        return $compras->map(function (Compra $c) {
            return [
                'ID' => $c->id,
                'Fecha' => optional($c->fecha)->format('Y-m-d H:i:s'),
                'Proveedor' => $c->proveedor?->nombre ?? '—',
                'Usuario' => $c->usuario?->name ?? '—',
                'Subtotal' => (float) $c->subtotal_bruto,
                'Descuento %' => (float) $c->descuento_porcentaje,
                'Descuento $' => (float) $c->descuento_monto_total,
                'Total' => (float) $c->total,
            ];
        })->toArray();
    }

    public function comprasExportGroupedRows(array $filtros): array
    {
        $payload = $this->compras($filtros);
        $groupBy = $payload['group_by'] ?? null;
        $modo = $payload['modo'] ?? 'detalle';

        if ($modo === 'resumen' && $groupBy) {
            $rows = [];
            foreach (($payload['resumen'] ?? []) as $r) {
                if ($groupBy === 'producto') {
                    $rows[] = [
                        'Producto' => $r['producto'],
                        'Categoría' => $r['categoria'],
                        'Unidades' => $r['unidades'],
                        'Gasto' => $r['gasto'],
                        'N° Compras' => $r['compras'],
                    ];
                } elseif ($groupBy === 'categoria') {
                    $rows[] = [
                        'Categoría' => $r['categoria'],
                        'Unidades' => $r['unidades'],
                        'Gasto' => $r['gasto'],
                        'N° Compras' => $r['compras'],
                    ];
                } elseif ($groupBy === 'proveedor') {
                    $rows[] = [
                        'Proveedor' => $r['proveedor'],
                        'Operaciones' => $r['cantidad'],
                        'Total Compras' => $r['total'],
                    ];
                } elseif ($groupBy === 'usuario') {
                    $rows[] = [
                        'Usuario' => $r['usuario'],
                        'Operaciones' => $r['cantidad'],
                        'Total Compras' => $r['total'],
                    ];
                }
            }
            return $rows;
        }

        $rows = [];
        foreach (($payload['grouped'] ?? []) as $g) {
            $rows[] = ['GRUPO' => $g['label']];

            if (in_array($groupBy, ['proveedor', 'usuario'], true)) {
                foreach ($g['rows'] as $c) {
                    $rows[] = [
                        'ID' => $c->id,
                        'Fecha' => optional($c->fecha)->format('Y-m-d H:i:s'),
                        'Proveedor' => $c->proveedor?->nombre ?? '—',
                        'Usuario' => $c->usuario?->name ?? '—',
                        'Subtotal' => (float) $c->subtotal_bruto,
                        'Descuento $' => (float) $c->descuento_monto_total,
                        'Total' => (float) $c->total,
                    ];
                }
                $rows[] = [
                    'ID' => 'SUBTOTAL',
                    'Fecha' => '',
                    'Proveedor' => '',
                    'Usuario' => '',
                    'Subtotal' => (float) ($g['subtotal']['bruto'] ?? 0),
                    'Descuento $' => (float) ($g['subtotal']['descuento'] ?? 0),
                    'Total' => (float) ($g['subtotal']['total'] ?? 0),
                ];
            } else {
                foreach ($g['rows'] as $r) {
                    $rows[] = [
                        'Compra ID' => $r['compra_id'],
                        'Fecha' => $r['fecha'],
                        'Proveedor' => $r['proveedor'],
                        'Usuario' => $r['usuario'],
                        'Producto' => $r['producto'],
                        'Categoría' => $r['categoria'],
                        'Unidades' => $r['unidades'],
                        'Gasto' => $r['gasto'],
                    ];
                }
                $rows[] = [
                    'Compra ID' => 'SUBTOTAL',
                    'Fecha' => '',
                    'Proveedor' => '',
                    'Usuario' => '',
                    'Producto' => '',
                    'Categoría' => '',
                    'Unidades' => (int) ($g['subtotal']['unidades'] ?? 0),
                    'Gasto' => (float) ($g['subtotal']['gasto'] ?? 0),
                ];
            }
            $rows[] = [];
        }

        return $rows;
    }

    /**
     * Reporte de movimientos de inventario.
     *
     * @param array{inicio:Carbon, fin:Carbon, tipo?:string|null, producto_id?:int|null, lote_id?:int|null, user_id?:int|null, origen?:string|null, motivo?:string|null} $filtros
     */
    public function movimientosInventario(array $filtros): array
    {
        $inicio = $filtros['inicio'];
        $fin = $filtros['fin'];

        $query = MovimientoInventario::query()
            ->with(['producto', 'lote', 'usuario'])
            ->whereBetween('fecha_movimiento', [$inicio, $fin])
            ->orderBy('fecha_movimiento', 'desc');

        if (!empty($filtros['tipo'])) {
            $query->where('tipo', (string) $filtros['tipo']);
        }
        if (!empty($filtros['producto_id'])) {
            $query->where('producto_id', (int) $filtros['producto_id']);
        }
        if (!empty($filtros['lote_id'])) {
            $query->where('lote_id', (int) $filtros['lote_id']);
        }
        if (!empty($filtros['user_id'])) {
            $query->where('user_id', (int) $filtros['user_id']);
        }
        if (!empty($filtros['origen'])) {
            $query->where('origen', (string) $filtros['origen']);
        }
        if (!empty($filtros['motivo'])) {
            $query->where('motivo', 'like', '%' . $filtros['motivo'] . '%');
        }

        $movimientos = $query->get();

        $totalEntradas = (int) $movimientos->where('tipo', 'entrada')->sum('cantidad');
        $totalSalidas = (int) abs($movimientos->where('tipo', 'salida')->sum('cantidad'));
        $totalAjustes = (int) $movimientos->where('tipo', 'ajuste')->sum('cantidad');

        $movimientosPorTipo = $movimientos
            ->groupBy('tipo')
            ->map(fn (Collection $movsTipo, $tipo) => [
                'tipo' => (string) $tipo,
                'cantidad' => (int) $movsTipo->count(),
                'total_unidades' => (int) $movsTipo->sum('cantidad'),
            ])
            ->values();

        $movimientosPorOrigen = $movimientos
            ->groupBy('origen')
            ->map(fn (Collection $movs, $origen) => [
                'origen' => (string) ($origen ?: '—'),
                'cantidad' => (int) $movs->count(),
                'total_unidades' => (int) $movs->sum('cantidad'),
            ])
            ->values();

        return [
            'movimientos' => $movimientos,
            'totalEntradas' => $totalEntradas,
            'totalSalidas' => $totalSalidas,
            'totalAjustes' => $totalAjustes,
            'movimientosPorTipo' => $movimientosPorTipo,
            'movimientosPorOrigen' => $movimientosPorOrigen,
        ];
    }

    public function movimientosExportRows(array $filtros): array
    {
        $payload = $this->movimientosInventario($filtros);
        /** @var Collection $movs */
        $movs = $payload['movimientos'];

        return $movs->map(function (MovimientoInventario $m) {
            return [
                'Fecha' => optional($m->fecha_movimiento)->format('Y-m-d H:i:s'),
                'Tipo' => $m->tipo,
                'Producto' => $m->producto?->nombre ?? '—',
                'Lote' => $m->lote?->numero_lote ?? '—',
                'Cantidad' => (int) $m->cantidad,
                'Saldo Anterior' => (int) $m->saldo_anterior,
                'Saldo Nuevo' => (int) $m->saldo_nuevo,
                'Origen' => $m->origen ?? '—',
                'Origen ID' => $m->origen_id ?? null,
                'Motivo' => $m->motivo ?? '—',
                'Usuario' => $m->usuario?->name ?? '—',
            ];
        })->toArray();
    }

    /**
     * Top productos más vendidos (unidades base) en el rango.
     *
     * @param array{inicio:Carbon, fin:Carbon, limite:int} $filtros
     */
    public function productosMasVendidos(array $filtros)
    {
        $inicio = $filtros['inicio'];
        $fin = $filtros['fin'];
        $limite = (int) ($filtros['limite'] ?? 20);

        // Nota: usamos cantidad_unidades_base por integridad de inventario.
        return DB::table('detalle_venta')
            ->join('ventas', 'detalle_venta.venta_id', '=', 'ventas.id')
            ->join('productos', 'detalle_venta.producto_id', '=', 'productos.id')
            ->leftJoin('categorias', 'productos.categoria_id', '=', 'categorias.id')
            ->whereBetween('ventas.fecha', [$inicio, $fin])
            ->where('ventas.estado', 'completada')
            ->select(
                'productos.id',
                'productos.nombre',
                DB::raw('COALESCE(categorias.nombre, "Sin categoría") as categoria'),
                DB::raw('SUM(detalle_venta.cantidad_unidades_base) as total_vendido'),
                DB::raw('SUM(detalle_venta.subtotal) as total_ingresos'),
                DB::raw('COUNT(DISTINCT detalle_venta.venta_id) as numero_ventas')
            )
            ->groupBy('productos.id', 'productos.nombre', 'categorias.nombre')
            ->orderByDesc('total_vendido')
            ->limit($limite)
            ->get();
    }

    public function productosMasVendidosExportRows(array $filtros): array
    {
        $rows = $this->productosMasVendidos($filtros);

        return collect($rows)->map(function ($r) {
            return [
                'Producto' => (string) $r->nombre,
                'Categoría' => (string) $r->categoria,
                'Unidades (base)' => (int) $r->total_vendido,
                'Ingresos' => (float) $r->total_ingresos,
                'N° Ventas' => (int) $r->numero_ventas,
            ];
        })->toArray();
    }

    /**
     * Reporte de lotes (vencimientos/stock/bloqueos).
     */
    public function lotes(array $filtros): array
    {
        $q = Lote::query()->with(['producto', 'proveedor', 'compra']);

        if (!empty($filtros['producto_id'])) {
            $q->where('producto_id', (int) $filtros['producto_id']);
        }
        if (!empty($filtros['proveedor_id'])) {
            $q->where('proveedor_id', (int) $filtros['proveedor_id']);
        }
        if (!empty($filtros['estado'])) {
            $q->where('estado', (string) $filtros['estado']);
        }
        if (array_key_exists('activo', $filtros) && $filtros['activo'] !== null) {
            $q->where('activo', (bool) $filtros['activo']);
        }
        if (!empty($filtros['con_stock'])) {
            $q->where('stock_actual', '>', 0);
        }
        if (!empty($filtros['vencidos'])) {
            $q->whereNotNull('fecha_vencimiento')->whereDate('fecha_vencimiento', '<', today());
        }
        if (!empty($filtros['proximos_dias'])) {
            $dias = (int) $filtros['proximos_dias'];
            $q->whereNotNull('fecha_vencimiento')
                ->whereDate('fecha_vencimiento', '>=', today())
                ->whereDate('fecha_vencimiento', '<=', today()->addDays($dias));
        }

        if (!empty($filtros['ingreso_inicio'])) {
            $q->where('fecha_ingreso', '>=', $filtros['ingreso_inicio']);
        }
        if (!empty($filtros['ingreso_fin'])) {
            $q->where('fecha_ingreso', '<=', $filtros['ingreso_fin']);
        }

        $lotes = $q->orderBy('fecha_ingreso', 'desc')->get();

        $totalLotes = (int) $lotes->count();
        $stockTotal = (int) $lotes->sum('stock_actual');
        $valorTotal = (float) $lotes->sum(fn (Lote $l) => ((float) ($l->precio_compra ?? 0)) * (int) ($l->stock_actual ?? 0));

        $hoy = today();
        $limiteProx = today()->addDays(30);

        $lotesVencidos = (int) $lotes->filter(function (Lote $l) use ($hoy) {
            if (empty($l->fecha_vencimiento)) {
                return false;
            }
            try {
                return Carbon::parse($l->fecha_vencimiento)->startOfDay()->lt($hoy);
            } catch (\Throwable $e) {
                return false;
            }
        })->count();

        $lotesProximos = (int) $lotes->filter(function (Lote $l) use ($hoy, $limiteProx) {
            if (empty($l->fecha_vencimiento)) {
                return false;
            }
            try {
                $fv = Carbon::parse($l->fecha_vencimiento)->startOfDay();
                return $fv->gte($hoy) && $fv->lte($limiteProx);
            } catch (\Throwable $e) {
                return false;
            }
        })->count();

        $lotesBloqueados = (int) $lotes->filter(function (Lote $l) {
            return isset($l->estado) && (string) $l->estado === 'bloqueado';
        })->count();

        return [
            'lotes' => $lotes,
            'totalLotes' => $totalLotes,
            'stockTotal' => $stockTotal,
            'valorTotal' => round($valorTotal, 2),
            'lotesVencidos' => $lotesVencidos,
            'lotesProximos' => $lotesProximos,
            'lotesBloqueados' => $lotesBloqueados,
        ];
    }

    public function lotesExportRows(array $filtros): array
    {
        $payload = $this->lotes($filtros);
        /** @var Collection $lotes */
        $lotes = $payload['lotes'];

        return $lotes->map(function (Lote $l) {
            $valor = ((float) ($l->precio_compra ?? 0)) * (int) ($l->stock_actual ?? 0);

            $venc = null;
            if (!empty($l->fecha_vencimiento)) {
                try {
                    $venc = Carbon::parse($l->fecha_vencimiento)->format('Y-m-d');
                } catch (\Throwable $e) {
                    $venc = (string) $l->fecha_vencimiento;
                }
            }
            return [
                'ID' => $l->id,
                'Producto' => $l->producto?->nombre ?? '—',
                'Proveedor' => $l->proveedor?->nombre ?? '—',
                'Compra ID' => $l->compra_id,
                'Lote' => $l->numero_lote,
                'Vencimiento' => $venc,
                'Ingreso' => !empty($l->fecha_ingreso)
                    ? (function () use ($l) {
                        try {
                            return Carbon::parse($l->fecha_ingreso)->format('Y-m-d H:i:s');
                        } catch (\Throwable $e) {
                            return (string) $l->fecha_ingreso;
                        }
                    })()
                    : null,
                'Stock Inicial' => (int) $l->stock_inicial,
                'Stock Actual' => (int) $l->stock_actual,
                'Precio Compra' => (float) ($l->precio_compra ?? 0),
                'Valor' => round($valor, 2),
                'Estado' => $l->estado,
                'Activo' => $l->activo ? 'Sí' : 'No',
            ];
        })->toArray();
    }

    /**
     * Flujo de caja derivado (ventas vs compras) — mientras no exista módulo de caja.
     * Nota: compras aquí son "gasto" contable, NO necesariamente pago real.
     */
    public function flujoCaja(array $filtros): array
    {
        $inicio = $filtros['inicio'];
        $fin = $filtros['fin'];

        $ventas = Venta::query()
            ->where('estado', 'completada')
            ->whereBetween('fecha', [$inicio, $fin]);

        if (!empty($filtros['user_id'])) {
            $ventas->where('user_id', (int) $filtros['user_id']);
        }

        $ingresosPorMetodo = (clone $ventas)
            ->selectRaw('metodo_pago as metodo, SUM(total) as total, COUNT(*) as cantidad')
            ->groupBy('metodo_pago')
            ->orderByDesc(DB::raw('SUM(total)'))
            ->get()
            ->map(fn ($r) => [
                'metodo' => (string) ($r->metodo ?? '—'),
                'cantidad' => (int) $r->cantidad,
                'total' => (float) $r->total,
            ]);

        $totalIngresos = (float) (clone $ventas)->sum('total');

        $compras = Compra::query()
            ->where('estado', 'recibida')
            ->whereBetween('fecha', [$inicio, $fin]);

        if (!empty($filtros['user_id'])) {
            $compras->where('user_id', (int) $filtros['user_id']);
        }

        $totalCompras = (float) (clone $compras)->sum('total');
        $neto = round($totalIngresos - $totalCompras, 2);

        return [
            'ingresosPorMetodo' => $ingresosPorMetodo,
            'totalIngresos' => round($totalIngresos, 2),
            'totalCompras' => round($totalCompras, 2),
            'neto' => $neto,
        ];
    }

    /**
     * Reporte especializado de ajustes de inventario.
     */
    public function ajustesInventario(array $filtros): array
    {
        $filtros['tipo'] = 'ajuste';
        $payload = $this->movimientosInventario($filtros);
        /** @var Collection $movimientos */
        $movimientos = $payload['movimientos'];

        $ajustesPorMotivo = $movimientos
            ->groupBy('motivo')
            ->map(fn (Collection $m, $motivo) => [
                'motivo' => (string) ($motivo ?: '—'),
                'cantidad' => (int) $m->count(),
                'total_unidades' => (int) $m->sum('cantidad'),
            ])
            ->values();

        return array_merge($payload, [
            'ajustesPorMotivo' => $ajustesPorMotivo,
        ]);
    }
}
