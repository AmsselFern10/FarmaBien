<?php

namespace App\Services;

use App\Models\{
    Venta,
    DetalleVenta,
    Lote,
    MovimientoInventario,
    PresentacionProducto,
    Producto,
    Receta
};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Exception;

class VentaService
{
    /* =====================================================
     * CREAR VENTA
     * ===================================================== */

    /**
     * Procesar una venta completa (cabecera + detalles + movimientos).
     *
     * Reglas:
     * - Todo se registra en UNIDADES BASE (movimientos y stock por lote).
     * - Detalle guarda snapshot de presentación + unidades base.
     * - Descuento por línea: porcentaje por item.
     * - Descuento global: porcentaje sobre el neto (después de descuentos por línea).
     * - Fecha: si viene en $data['fecha'], se respeta (incluye hora).
     */
    public function procesarVenta(array $data): Venta
    {
        return DB::transaction(function () use ($data) {

            $this->validarDatosVenta($data);
            $this->validarRecetasMedicas($data);

            $fecha = $data['fecha'] ?? now();

            $descuentoGlobalPct = $this->normalizarPorcentaje(
                $data['descuento_porcentaje'] ?? $data['descuento'] ?? 0,
                'El descuento total debe estar entre 0% y 100%.'
            );

            $venta = Venta::create([
                'cliente_id' => $data['cliente_id'] ?? null,
                'user_id' => Auth::id(),
                'metodo_pago' => $data['metodo_pago'],
                'estado' => 'completada',
                'fecha' => $fecha,

                'subtotal_bruto' => 0,
                'descuento_porcentaje' => $descuentoGlobalPct,
                'descuento_monto_total' => 0,
                'total' => 0,

                // Caja / pago
                'monto_recibido' => Arr::get($data, 'monto_recibido'),
                'cambio' => null, // se calcula al final si aplica
                'referencia_pago' => Arr::get($data, 'referencia_pago'),

                'observaciones' => $data['observaciones'] ?? null,
            ]);

            $totales = [
                'bruto' => 0.0,
                'descuento_linea' => 0.0,
                'neto' => 0.0,
            ];

            foreach ($data['productos'] as $item) {
                $t = $this->procesarDetalleVenta($venta, $item);

                $totales['bruto'] += $t['bruto'];
                $totales['descuento_linea'] += $t['descuento'];
                $totales['neto'] += $t['neto'];
            }

            // Descuento GLOBAL (% sobre neto)
            $descuentoGlobalMonto = round($totales['neto'] * ($descuentoGlobalPct / 100), 2);
            $totalFinal = round($totales['neto'] - $descuentoGlobalMonto, 2);

            // Calcular cambio en efectivo (si aplica)
            $montoRecibido = Arr::get($data, 'monto_recibido');
            $cambio = null;

            if ($this->esPagoEfectivo((string) $data['metodo_pago']) && $montoRecibido !== null && $montoRecibido !== '') {
                $montoRecibido = (float) $montoRecibido;

                if ($montoRecibido < $totalFinal) {
                    throw new Exception("Monto recibido insuficiente. Recibido: {$montoRecibido}, Total: {$totalFinal}");
                }

                $cambio = round($montoRecibido - $totalFinal, 2);
            }

            $venta->update([
                'subtotal_bruto' => round($totales['bruto'], 2),
                'descuento_porcentaje' => $descuentoGlobalPct,
                'descuento_monto_total' => round($totales['descuento_linea'] + $descuentoGlobalMonto, 2),
                'total' => $totalFinal,
                'monto_recibido' => $montoRecibido !== '' ? $montoRecibido : null,
                'cambio' => $cambio,
            ]);

            if (!empty($data['recetas']) && is_array($data['recetas'])) {
                $venta->recetas()->attach($data['recetas']);
            }

            return $venta->load(['detalles.lote', 'detalles.presentacion', 'recetas', 'cliente', 'usuario']);
        });
    }

    /* =====================================================
     * DETALLE DE VENTA (por lote)
     * ===================================================== */

    protected function procesarDetalleVenta(Venta $venta, array $item): array
    {
        // Lock del lote para evitar sobreventa concurrente
        $lote = Lote::with('producto')
            ->whereKey($item['lote_id'])
            ->lockForUpdate()
            ->firstOrFail();

        // Validar coherencia lote vs producto recibido
        if (!empty($item['producto_id']) && (int) $item['producto_id'] !== (int) $lote->producto_id) {
            throw new Exception("El lote seleccionado no corresponde al producto indicado.");
        }

        /* ===== Presentación / cantidades (UNIDADES BASE) ===== */
        $presentacion = null;
        $presentacionId = $item['presentacion_id'] ?? null;

        if ($presentacionId) {
            $presentacion = PresentacionProducto::findOrFail($presentacionId);

            if ((int) $presentacion->producto_id !== (int) $lote->producto_id) {
                throw new Exception("La presentación no pertenece al producto del lote.");
            }

            if (!$presentacion->activo) {
                throw new Exception("La presentación seleccionada está inactiva.");
            }

            $cantidadPresentaciones = (int) ($item['cantidad_presentaciones'] ?? 0);
            if ($cantidadPresentaciones < 1) {
                throw new Exception("Cantidad de presentaciones inválida.");
            }

            $unidadesPorPresentacion = (int) $presentacion->unidades_por_presentacion;
            if ($unidadesPorPresentacion < 1) {
                throw new Exception("La presentación tiene unidades por presentación inválidas.");
            }

            $cantidadBase = $cantidadPresentaciones * $unidadesPorPresentacion;
            $tipoPresentacion = $presentacion->nombre;
        } else {
            // Unidad base
            $cantidadBase = (int) ($item['cantidad'] ?? 0);
            if ($cantidadBase < 1) {
                throw new Exception("Cantidad inválida.");
            }

            $cantidadPresentaciones = $cantidadBase; // 1 unidad por presentación (unidad base)
            $unidadesPorPresentacion = 1;
            $tipoPresentacion = 'Unidad';
        }

        $this->validarLoteParaVenta($lote, $cantidadBase);

        /* ===== Precios ===== */
        // Precio unitario SIEMPRE es por UNIDAD BASE.
        $precioUnitario = $item['precio_unitario'] ?? null;

        // Si la UI algún día manda precio por presentación, lo soportamos sin romper:
        if ($presentacion && array_key_exists('precio_presentacion', $item) && $item['precio_presentacion'] !== null && $item['precio_presentacion'] !== '') {
            $precioUnitario = $presentacion->calcularPrecioUnitario((float) $item['precio_presentacion']);
        }

        $precioUnitario = (float) ($precioUnitario ?? $lote->producto->precio_venta);

        if ($precioUnitario < 0) {
            throw new Exception("El precio unitario no puede ser negativo.");
        }

        $descuentoPct = $this->normalizarPorcentaje(
            $item['descuento_porcentaje'] ?? $item['descuento'] ?? 0,
            'El descuento por producto debe estar entre 0% y 100%.'
        );

        $subtotalBruto = round($cantidadBase * $precioUnitario, 2);
        $descuentoMonto = round($subtotalBruto * ($descuentoPct / 100), 2);
        $subtotalNeto = round($subtotalBruto - $descuentoMonto, 2);

        DetalleVenta::create([
            'venta_id' => $venta->id,
            'producto_id' => $lote->producto_id,
            'lote_id' => $lote->id,

            // Snapshot
            'numero_lote' => $lote->numero_lote,

            // Presentación (capa comercial)
            'presentacion_id' => $presentacion?->id,
            'tipo_presentacion' => $tipoPresentacion,
            'unidades_por_presentacion' => $unidadesPorPresentacion,
            'cantidad_presentaciones' => $cantidadPresentaciones,

            // Inventario (unidades base)
            'cantidad_unidades_base' => $cantidadBase,

            // Monetarios
            'precio_unitario' => $precioUnitario,
            'subtotal_bruto' => $subtotalBruto,
            'descuento_porcentaje' => $descuentoPct,
            'descuento_monto' => $descuentoMonto,
            'subtotal' => $subtotalNeto,
        ]);

        MovimientoInventario::create([
            'lote_id' => $lote->id,
            'user_id' => Auth::id(),
            'tipo' => 'salida',
            // Cantidad en UNIDADES BASE (positiva). El modelo normaliza a negativa para salidas.
            'cantidad' => $cantidadBase,
            'origen' => 'venta',
            'origen_id' => $venta->id,
            'motivo' => $presentacion
                ? "Venta #{$venta->id} - {$cantidadPresentaciones} {$tipoPresentacion}(s) x {$unidadesPorPresentacion} = {$cantidadBase} unidades - Lote {$lote->numero_lote}"
                : "Venta #{$venta->id} - {$cantidadBase} unidades - Lote {$lote->numero_lote}",
            'fecha_movimiento' => $venta->fecha ?? now(),
        ]);

        return [
            'bruto' => $subtotalBruto,
            'descuento' => $descuentoMonto,
            'neto' => $subtotalNeto,
        ];
    }

    /* =====================================================
     * VALIDACIONES
     * ===================================================== */

    protected function validarDatosVenta(array $data): void
    {
        if (!isset($data['productos']) || !is_array($data['productos']) || count($data['productos']) < 1) {
            throw new Exception('Debe proporcionar al menos un producto.');
        }

        foreach ($data['productos'] as $index => $item) {
            if (empty($item['lote_id'])) {
                throw new Exception("Falta lote en el ítem #{$index}.");
            }

            if (empty($item['producto_id'])) {
                throw new Exception("Falta producto en el ítem #{$index}.");
            }

            // Validar presentación si viene
            if (!empty($item['presentacion_id'])) {
                $presentacion = PresentacionProducto::find($item['presentacion_id']);

                if (!$presentacion) {
                    throw new Exception("La presentación del ítem #{$index} no existe.");
                }

                if (!$presentacion->activo) {
                    throw new Exception("La presentación '{$presentacion->nombre}' del ítem #{$index} está inactiva.");
                }

                if ((int) $presentacion->producto_id !== (int) $item['producto_id']) {
                    throw new Exception("La presentación del ítem #{$index} no corresponde al producto.");
                }

                $cantidadPres = (int) ($item['cantidad_presentaciones'] ?? 0);
                if ($cantidadPres < 1) {
                    throw new Exception("La cantidad de presentaciones del ítem #{$index} debe ser mayor a 0.");
                }
            } else {
                $cantidad = (int) ($item['cantidad'] ?? 0);
                if ($cantidad < 1) {
                    throw new Exception("La cantidad del ítem #{$index} debe ser mayor a 0.");
                }
            }

            if (isset($item['precio_unitario']) && $item['precio_unitario'] !== null && (float) $item['precio_unitario'] < 0) {
                throw new Exception("El precio unitario del ítem #{$index} no puede ser negativo.");
            }

            // Descuento por línea
            if (isset($item['descuento_porcentaje']) || isset($item['descuento'])) {
                $this->normalizarPorcentaje(
                    $item['descuento_porcentaje'] ?? $item['descuento'] ?? 0,
                    "El descuento del ítem #{$index} debe estar entre 0% y 100%."
                );
            }
        }

        // Descuento global
        if (isset($data['descuento_porcentaje']) || isset($data['descuento'])) {
            $this->normalizarPorcentaje(
                $data['descuento_porcentaje'] ?? $data['descuento'] ?? 0,
                'El descuento total debe estar entre 0% y 100%.'
            );
        }
    }

    protected function validarLoteParaVenta(Lote $lote, int $cantidad): void
    {
        if (!$lote->activo) {
            throw new Exception("El lote {$lote->numero_lote} del producto {$lote->producto->nombre} está inactivo.");
        }

        // IMPORTANTÍSIMO:
        // fecha_vencimiento está casteada como "date" (00:00:00). Para permitir vender el día de vencimiento,
        // consideramos vencido solo si fecha_vencimiento < HOY.
        if ($lote->fecha_vencimiento && $lote->fecha_vencimiento->isBefore(today())) {
            throw new Exception(
                "El lote {$lote->numero_lote} del producto {$lote->producto->nombre} está vencido (Vencimiento: {$lote->fecha_vencimiento->format('d/m/Y')})."
            );
        }

        if (!$lote->tieneStock($cantidad)) {
            throw new Exception(
                "Stock insuficiente para {$lote->producto->nombre}. " .
                "Disponible en lote {$lote->numero_lote}: {$lote->stock_actual}, Solicitado: {$cantidad}"
            );
        }
    }

    protected function normalizarPorcentaje($valor, string $mensajeError): float
    {
        $pct = (float) ($valor ?? 0);

        if ($pct < 0 || $pct > 100) {
            throw new Exception($mensajeError);
        }

        return $pct;
    }

    protected function esPagoEfectivo(string $metodoPago): bool
    {
        $m = mb_strtolower(trim($metodoPago));
        return str_contains($m, 'efectivo') || $m === 'cash';
    }

    /**
     * Validar que los productos que requieren receta tengan una asociada
     *
     * @param array $data
     * @throws Exception
     */
    protected function validarRecetasMedicas(array $data): void
    {
        $productosIds = collect($data['productos'])->pluck('producto_id')->unique()->values();

        $productosConReceta = Producto::whereIn('id', $productosIds)
            ->where('requiere_receta', true)
            ->get();

        if ($productosConReceta->isNotEmpty()) {
            if (empty($data['recetas'])) {
                $nombres = $productosConReceta->pluck('nombre')->join(', ');
                throw new Exception("Los siguientes productos requieren receta médica: {$nombres}");
            }

            $recetasIds = $data['recetas'];
            $recetasValidas = Receta::whereIn('id', $recetasIds)->count();

            if ($recetasValidas !== count($recetasIds)) {
                throw new Exception("Una o más recetas médicas no son válidas.");
            }
        }
    }

    
    /* =====================================================
     * REVERSAS DE MOVIMIENTOS (VENTAS)
     * ===================================================== */

    /**
     * Revierte (compensa) los movimientos de salida de una venta creando movimientos de entrada.
     *
     * - No se editan ni eliminan movimientos (append-only).
     * - Si existe el movimiento original (origen=venta), se referencia con reversa_de_id para trazabilidad.
     *
     * @throws Exception
     */
    protected function reversarMovimientosVenta(Venta $venta, string $origenReversa, string $motivo): void
    {
        // Movimientos originales de salida de la venta (1 por ítem, según procesarDetalleVenta)
        $movimientosSalida = MovimientoInventario::query()
            ->where('origen', 'venta')
            ->where('origen_id', $venta->id)
            ->where('tipo', 'salida')
            ->orderBy('id')
            ->get()
            ->groupBy('lote_id');

        foreach ($venta->detalles as $detalle) {
            $cantidadBase = (int) $detalle->cantidad_unidades_base;

            $movOriginal = null;
            if (isset($movimientosSalida[$detalle->lote_id]) && $movimientosSalida[$detalle->lote_id]->isNotEmpty()) {
                $movOriginal = $movimientosSalida[$detalle->lote_id]->shift();
            }

            MovimientoInventario::create([
                'lote_id' => $detalle->lote_id,
                'user_id' => Auth::id(),
                'tipo' => 'entrada',
                'cantidad' => $cantidadBase, // El modelo normaliza (+) para entrada.
                'reversa_de_id' => $movOriginal?->id,
                'origen' => $origenReversa,
                'origen_id' => $venta->id,
                'motivo' => $motivo,
                'fecha_movimiento' => now(),
            ]);
        }
    }

/* =====================================================
     * ANULAR VENTA
     * ===================================================== */

    public function anularVenta(int $ventaId, string $motivo): Venta
    {
        return DB::transaction(function () use ($ventaId, $motivo) {

            $venta = Venta::with('detalles')->findOrFail($ventaId);

            if (!$venta->puedeAnularse()) {
                throw new Exception("La venta #{$ventaId} no puede ser anulada porque ya está anulada o fue reemplazada.");
            }

            $this->reversarMovimientosVenta($venta, 'anulacion_venta', "Anulación de venta #{$venta->id}: {$motivo}");

            $venta->update([
                'estado' => 'anulada',
                'anulado_por' => Auth::id(),
                'fecha_anulacion' => now(),
                'motivo_anulacion' => $motivo,
            ]);

            return $venta->fresh(['detalles', 'anuladoPor', 'cliente', 'usuario']);
        });
    }

    /* =====================================================
     * CONSULTAS
     * ===================================================== */

    public function ventasDelDia()
    {
        return Venta::with(['cliente', 'usuario', 'detalles.producto'])
            ->whereDate('fecha', today())
            ->completadas()
            ->orderBy('fecha', 'desc')
            ->get();
    }

    public function totalVentasDelDia(): float
    {
        return (float) Venta::whereDate('fecha', today())
            ->completadas()
            ->sum('total');
    }

    public function ventasDelUsuario(int $userId, ?string $fechaInicio = null, ?string $fechaFin = null)
    {
        $query = Venta::with(['cliente', 'detalles.producto'])
            ->where('user_id', $userId);

        if ($fechaInicio) {
            $query->whereDate('fecha', '>=', $fechaInicio);
        }

        if ($fechaFin) {
            $query->whereDate('fecha', '<=', $fechaFin);
        }

        return $query->orderBy('fecha', 'desc')->get();
    }

    public function buscarProductosParaVenta(string $termino)
    {
        return Producto::with(['categoria', 'presentacionesActivas', 'lotes' => function ($query) {
                $query->select(['id','producto_id','numero_lote','fecha_vencimiento','stock_actual','precio_compra','activo','estado','bloqueado_at'])
                    ->where('activo', true)
                    ->whereNull('bloqueado_at')
                    ->where('estado', '!=', 'bloqueado')
                    ->whereDate('fecha_vencimiento', '>=', today())
                    ->where('stock_actual', '>', 0)
                    ->orderBy('fecha_vencimiento', 'asc'); // FEFO
            }])
            ->activos()
            ->where(function ($query) use ($termino) {
                $query->where('nombre', 'like', "%{$termino}%")
                    ->orWhere('codigo_barra', 'like', "%{$termino}%");
            })
            ->get()
            ->filter(function ($producto) {
                return $producto->lotes->isNotEmpty();
            });
    }

    /* =====================================================
     * MODIFICAR VENTA (anula + recrea)
     * ===================================================== */

    public function modificarVenta(int $ventaId, array $data, string $motivo): Venta
    {
        return DB::transaction(function () use ($ventaId, $data, $motivo) {

            $ventaOriginal = Venta::with('detalles')->findOrFail($ventaId);

            if (!$ventaOriginal->puedeModificarse()) {
                throw new Exception(
                    "La venta #{$ventaId} no puede modificarse. " .
                    "Estado: {$ventaOriginal->estado}, " .
                    "Reemplazada: " . ($ventaOriginal->reemplazada_por ? 'Sí' : 'No')
                );
            }

            // Revertir inventario de la venta original (reversa de movimientos)
            $this->reversarMovimientosVenta($ventaOriginal, 'modificacion_venta', "Modificación de venta #{$ventaOriginal->id}: {$motivo}");

            // Crear la nueva venta con los datos corregidos
            $nuevaVenta = $this->procesarVenta($data);

            // Marcar la original como anulada y reemplazada
            $ventaOriginal->update([
                'estado' => 'anulada',
                'anulado_por' => Auth::id(),
                'fecha_anulacion' => now(),
                'motivo_anulacion' => "Modificada - Razón: {$motivo}. Nueva venta: #{$nuevaVenta->id}",
                'reemplazada_por' => $nuevaVenta->id,
            ]);

            // Marcar la nueva como modificación de la original
            $nuevaVenta->update([
                'venta_original_id' => $ventaOriginal->id,
            ]);

            return $nuevaVenta->load([
                'detalles.producto',
                'detalles.lote',
                'detalles.presentacion',
                'cliente',
                'usuario',
                'recetas',
                'ventaOriginal'
            ]);
        });
    }

    /* =====================================================
     * HISTORIAL DE MODIFICACIONES (cadena)
     * ===================================================== */

    public function historialModificacionesVenta(int $ventaId): Collection
    {
        $venta = Venta::with(['usuario', 'cliente'])->findOrFail($ventaId);

        $cadena = $this->cadenaModificaciones($venta);

        // Precargar usuario/cliente para evitar N+1
        $ids = $cadena->pluck('id')->values()->all();
        $ventas = Venta::with(['usuario', 'cliente'])
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        return collect($ids)->map(function ($id, $index) use ($ventas) {
            /** @var \App\Models\Venta $v */
            $v = $ventas[$id];

            return [
                'version' => $index + 1,
                'id' => $v->id,
                'fecha' => $v->fecha,
                'total' => $v->total,
                'estado' => $v->estado,
                'usuario' => $v->usuario?->name ?? 'N/A',
                'cliente' => $v->cliente ? $v->cliente->nombre : 'Público general',
                'es_original' => is_null($v->venta_original_id),
                'es_activa' => is_null($v->reemplazada_por) && $v->estado === 'completada',
                'motivo_anulacion' => $v->motivo_anulacion,
            ];
        });
    }

    protected function cadenaModificaciones(Venta $venta): Collection
    {
        // Ir hacia atrás hasta la original
        $actual = $venta;

        while (!is_null($actual->venta_original_id)) {
            $actual = Venta::findOrFail($actual->venta_original_id);
        }

        $cadena = collect([$actual]);

        // Ir hacia adelante hasta la última modificación
        while (!is_null($actual->reemplazada_por)) {
            $actual = Venta::findOrFail($actual->reemplazada_por);
            $cadena->push($actual);
        }

        return $cadena->unique('id')->values();
    }
}
