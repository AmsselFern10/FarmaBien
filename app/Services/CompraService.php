<?php

namespace App\Services;

use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\Lote;
use App\Models\Producto;
use App\Models\PresentacionProducto;
use App\Models\MovimientoInventario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class CompraService
{
    /**
     * Normaliza el número de lote para evitar duplicados por espacios / mayúsculas.
     */
    protected function normalizarNumeroLote(?string $numero): string
    {
        $numero = trim((string) $numero);
        $numero = preg_replace('/\s+/', ' ', $numero);
        return mb_strtoupper($numero);
    }

    /**
     * Registrar una compra completa
     *
     * @param array $data Datos de la compra
     * @return Compra
     * @throws Exception
     */
        /**
     * Registrar una compra completa (con descuentos por línea y descuento global %)
     *
     * Campos esperados:
     * - descuento (porcentaje global 0..100)
     * - observaciones (opcional)
     * - productos[].descuento (porcentaje por producto 0..100)
     *
     * @throws Exception
     */
    public function registrarCompra(array $data): Compra
    {
        return DB::transaction(function () use ($data) {

            // 0. Validaciones
            $this->validarDatosCompra($data);

            $descuentoGlobalPct = round((float)($data['descuento'] ?? 0), 2);
            $observaciones = $data['observaciones'] ?? null;

            if ($descuentoGlobalPct < 0 || $descuentoGlobalPct > 100) {
                throw new Exception('El descuento global debe estar entre 0 y 100.');
            }

            // 1. Crear cabecera en 0, luego actualizamos con totales
            $compra = Compra::create([
                'proveedor_id' => $data['proveedor_id'],
                'user_id' => Auth::id(),
                'estado' => 'recibida',
                'fecha' => $data['fecha'] ?? now(),

                'subtotal_bruto' => 0,
                'descuento_porcentaje' => $descuentoGlobalPct,
                'descuento_monto_total' => 0,
                'total' => 0,

                'observaciones' => $observaciones,
            ]);

            $totales = [
                'bruto' => 0.0,      // suma subtotal_bruto por línea
                'descuento' => 0.0,  // suma descuento_monto por línea
                'neto' => 0.0,       // suma subtotal neto por línea (bruto - desc línea)
            ];

            // 2. Procesar cada detalle
            foreach ($data['productos'] as $item) {
                $t = $this->procesarDetalleCompra($compra, $item);
                $totales['bruto'] += $t['bruto'];
                $totales['descuento'] += $t['descuento'];
                $totales['neto'] += $t['neto'];
            }

            $totales['bruto'] = round($totales['bruto'], 2);
            $totales['descuento'] = round($totales['descuento'], 2);
            $totales['neto'] = round($totales['neto'], 2);

            // 3. Descuento global aplicado sobre el NETO después de descuentos por línea
            $descuentoGlobalMonto = round($totales['neto'] * ($descuentoGlobalPct / 100), 2);
            $totalFinal = round($totales['neto'] - $descuentoGlobalMonto, 2);

            // 4. Actualizar cabecera
            $compra->update([
                'subtotal_bruto' => $totales['bruto'],
                'descuento_porcentaje' => $descuentoGlobalPct,
                'descuento_monto_total' => round($totales['descuento'] + $descuentoGlobalMonto, 2),
                'total' => $totalFinal,
                'observaciones' => $observaciones,
            ]);

            return $compra->load([
                'detalles.producto',
                'detalles.lote',
                'detalles.presentacion',
                'proveedor',
                'lotes'
            ]);
        });
    }

    /**
     * Procesar un detalle de compra con soporte para presentaciones
     *
     * IMPORTANTE:
     * - stock_inicial se guarda en UNIDADES BASE
     * - precio_unitario se guarda como precio por UNIDAD BASE
     */
        /**
     * Procesar un detalle de compra con soporte para presentaciones + descuento por producto (%)
     *
     * IMPORTANTE:
     * - stock_inicial se guarda en UNIDADES BASE
     * - detalle.precio_unitario se guarda como precio BRUTO por UNIDAD BASE
     * - detalle.subtotal_bruto = unidades_base * precio_unitario
     * - detalle.subtotal = NETO (ya con desc por producto)
     * - lote.precio_compra se guarda como costo unitario NETO (para costo promedio real)
     *
     * @return array{bruto:float,descuento:float,neto:float}
     * @throws Exception
     */
    protected function procesarDetalleCompra(Compra $compra, array $item): array
    {
        // 1) Producto
        $producto = Producto::findOrFail($item['producto_id']);

        // 2) Datos
        $presentacionId = $item['presentacion_id'] ?? null;
        $cantidadPresentaciones = (int)($item['cantidad_presentaciones'] ?? $item['cantidad'] ?? 1);

        if ($cantidadPresentaciones < 1) {
            throw new Exception("Cantidad inválida para el producto: {$producto->nombre}");
        }

        $precioUnitario = (float)($item['precio_unitario'] ?? $producto->precio_compra);
        if ($precioUnitario < 0) {
            throw new Exception("El precio unitario no puede ser negativo para: {$producto->nombre}");
        }

        $descuentoPct = round((float)($item['descuento'] ?? 0), 2);
        if ($descuentoPct < 0 || $descuentoPct > 100) {
            throw new Exception("El descuento por producto debe estar entre 0 y 100 para: {$producto->nombre}");
        }

        // 3) Presentación (snapshot)
        $unidadesPorPresentacion = 1;
        $tipoPresentacion = 'Unidad';

        if ($presentacionId) {
            $presentacion = PresentacionProducto::findOrFail($presentacionId);

            if ((int)$presentacion->producto_id !== (int)$producto->id) {
                throw new Exception("La presentación seleccionada no pertenece al producto: {$producto->nombre}");
            }

            if (!$presentacion->activo) {
                throw new Exception("La presentación '{$presentacion->nombre}' no está activa para: {$producto->nombre}");
            }

            $unidadesPorPresentacion = (int)$presentacion->unidades_por_presentacion;
            $tipoPresentacion = $presentacion->nombre;
        }

        // 4) Cantidad real en unidades base
        $cantidadUnidadesBase = (int)$cantidadPresentaciones * (int)$unidadesPorPresentacion;
        if ($cantidadUnidadesBase < 1) {
            throw new Exception("La cantidad en unidades base no puede ser menor a 1 para: {$producto->nombre}");
        }

        // 5) Cálculos monetarios (sobre unidades base)
        $subtotalBruto = round($cantidadUnidadesBase * $precioUnitario, 2);
        $descuentoMonto = round($subtotalBruto * ($descuentoPct / 100), 2);
        $subtotalNeto = round($subtotalBruto - $descuentoMonto, 2);

        // Costo unitario NETO para lote (evita costo promedio inflado)
        $costoUnitarioNeto = round($subtotalNeto / $cantidadUnidadesBase, 6);

        // 6) Lote (blindado por producto_id + numero_lote)
        $numeroLote = $this->normalizarNumeroLote($item['numero_lote'] ?? '');
        if ($numeroLote === '') {
            throw new Exception("El número de lote es obligatorio para el producto: {$producto->nombre}");
        }

        if (empty($item['fecha_vencimiento'])) {
            throw new Exception("La fecha de vencimiento es obligatoria para el lote {$numeroLote} del producto: {$producto->nombre}");
        }

        $lote = Lote::where('producto_id', $producto->id)
            ->where('numero_lote', $numeroLote)
            ->first();

        if ($lote) {
            $lote->update([
                'activo'            => true,
                'compra_id'         => $compra->id,
                'proveedor_id'      => $compra->proveedor_id,
                'fecha_vencimiento' => $item['fecha_vencimiento'],
                'stock_inicial'     => $cantidadUnidadesBase,
                'precio_compra'     => $costoUnitarioNeto, // NETO
            ]);
        } else {
            $lote = Lote::create([
                'producto_id'       => $producto->id,
                'compra_id'         => $compra->id,
                'proveedor_id'      => $compra->proveedor_id,
                'numero_lote'       => $numeroLote,
                'fecha_vencimiento' => $item['fecha_vencimiento'],
                'stock_inicial'     => $cantidadUnidadesBase,
                'precio_compra'     => $costoUnitarioNeto, // NETO
                'activo'            => true,
            ]);
        }

        // 7) Detalle de compra (snapshot + descuentos)
        DetalleCompra::create([
            'compra_id'                 => $compra->id,
            'producto_id'               => $producto->id,
            'lote_id'                   => $lote->id,

            'presentacion_id'           => $presentacionId,
            'tipo_presentacion'         => $tipoPresentacion,
            'unidades_por_presentacion' => $unidadesPorPresentacion,
            'cantidad_presentaciones'   => $cantidadPresentaciones,

            'cantidad_unidades_base'    => $cantidadUnidadesBase,

            'precio_unitario'           => $precioUnitario,   // BRUTO unitario base
            'subtotal_bruto'            => $subtotalBruto,
            'descuento_porcentaje'      => $descuentoPct,
            'descuento_monto'           => $descuentoMonto,
            'subtotal'                  => $subtotalNeto,     // NETO
        ]);

        // 8) Movimiento inventario (ENTRADA en unidades base)
        MovimientoInventario::create([
            'producto_id'      => $producto->id,
            'lote_id'          => $lote->id,
            'user_id'          => Auth::id(),
            'tipo'             => 'entrada',
            'cantidad'         => $cantidadUnidadesBase,
            'origen'           => 'compra',
            'origen_id'        => $compra->id,
            'motivo'           => $presentacionId
                ? "Compra #{$compra->id} - {$cantidadPresentaciones} {$tipoPresentacion}(s) x {$unidadesPorPresentacion} = {$cantidadUnidadesBase} unidades - Lote {$lote->numero_lote}"
                : "Compra #{$compra->id} - {$cantidadUnidadesBase} unidades - Lote {$lote->numero_lote}",
            'fecha_movimiento' => now(),
        ]);

        // opcional: último costo unitario neto en producto
        $producto->update([
            'precio_compra' => $costoUnitarioNeto,
        ]);

        return [
            'bruto' => $subtotalBruto,
            'descuento' => $descuentoMonto,
            'neto' => $subtotalNeto,
        ];
    }


    /**
     * Validar datos de compra
     *
     * @throws Exception
     */
        /**
     * Validar datos de compra (incluye descuento global y por producto)
     *
     * @throws Exception
     */
    protected function validarDatosCompra(array $data): void
    {
        if (empty($data['proveedor_id'])) {
            throw new Exception('El proveedor es obligatorio.');
        }

        if (!isset($data['productos']) || !is_array($data['productos']) || count($data['productos']) < 1) {
            throw new Exception('Debe proporcionar al menos un producto.');
        }

        $descuentoGlobal = (float)($data['descuento'] ?? 0);
        if ($descuentoGlobal < 0 || $descuentoGlobal > 100) {
            throw new Exception('El descuento global debe estar entre 0 y 100.');
        }

        foreach ($data['productos'] as $index => $item) {
            if (empty($item['producto_id'])) {
                throw new Exception("Falta el producto en el ítem #{$index}.");
            }

            $numeroLote = $this->normalizarNumeroLote($item['numero_lote'] ?? '');
            if ($numeroLote === '') {
                throw new Exception("El número de lote en el producto #{$index} es obligatorio.");
            }

            if (empty($item['fecha_vencimiento'])) {
                throw new Exception("La fecha de vencimiento en el producto #{$index} es obligatoria.");
            }

            if (!empty($item['presentacion_id'])) {
                $presentacion = PresentacionProducto::find($item['presentacion_id']);

                if (!$presentacion) {
                    throw new Exception("La presentación seleccionada en el producto #{$index} no existe.");
                }

                if (!$presentacion->activo) {
                    throw new Exception("La presentación '{$presentacion->nombre}' en el producto #{$index} no está activa.");
                }

                if ((int)$presentacion->producto_id !== (int)$item['producto_id']) {
                    throw new Exception("La presentación seleccionada no corresponde al producto en el ítem #{$index}.");
                }

                if (empty($item['cantidad_presentaciones']) && empty($item['cantidad'])) {
                    throw new Exception("Debe indicar cantidad de presentaciones en el producto #{$index}.");
                }
            }

            $cantidad = $item['cantidad_presentaciones'] ?? $item['cantidad'] ?? 0;
            if ((int)$cantidad < 1) {
                throw new Exception("La cantidad en el producto #{$index} debe ser mayor a 0.");
            }

            $precioUnitario = (float)($item['precio_unitario'] ?? 0);
            if ($precioUnitario < 0) {
                throw new Exception("El precio unitario en el producto #{$index} no puede ser negativo.");
            }

            $descLinea = (float)($item['descuento'] ?? 0);
            if ($descLinea < 0 || $descLinea > 100) {
                throw new Exception("El descuento por producto en el ítem #{$index} debe estar entre 0 y 100.");
            }
        }
    }


    /**
     * Anular una compra
     */
    public function anularCompra(int $compraId, string $motivo): Compra
    {
        return DB::transaction(function () use ($compraId, $motivo) {

            $compra = Compra::with(['detalles', 'lotes'])->findOrFail($compraId);

            if (!$compra->puedeAnularse()) {
                throw new Exception("La compra #{$compraId} ya está anulada.");
            }

            foreach ($compra->lotes as $lote) {
                if ($lote->stock_actual < $lote->stock_inicial) {
                    throw new Exception(
                        "No se puede anular la compra porque el lote {$lote->numero_lote} ya tiene productos vendidos."
                    );
                }
            }

            foreach ($compra->lotes as $lote) {
                $lote->update(['activo' => false]);

                MovimientoInventario::create([
                    'producto_id'      => $lote->producto_id,
                    'lote_id'          => $lote->id,
                    'user_id'          => Auth::id(),
                    'tipo'             => 'salida',
                    'cantidad'         => -$lote->stock_inicial,
                    'origen'           => 'anulacion_compra',
                    'origen_id'        => $compra->id,
                    'motivo'           => "Anulación de compra #{$compra->id}: {$motivo}",
                    'fecha_movimiento' => now(),
                ]);
            }

            // Recalcular precio_compra de los productos afectados
            foreach ($compra->detalles as $detalle) {
                $producto = $detalle->producto;

                $ultimoPrecio = DetalleCompra::where('producto_id', $producto->id)
                    ->whereHas('compra', function ($q) {
                        $q->where('estado', 'recibida')
                            ->whereNull('reemplazada_por');
                    })
                    ->orderBy('id', 'desc')
                    ->first();

                $producto->update([
                    'precio_compra' => $ultimoPrecio?->precio_unitario
                ]);
            }

            $compra->update([
                'estado'          => 'anulada',
                'anulado_por'     => Auth::id(),
                'fecha_anulacion' => now(),
                'motivo_anulacion'=> $motivo,
            ]);

            return $compra->fresh(['detalles', 'lotes', 'anuladoPor']);
        });
    }

    /**
     * Modificar una compra existente:
     * 1) Revierte inventario de lotes originales (si no hay ventas)
     * 2) Desactiva lotes originales
     * 3) Crea nueva compra (registrarCompra) que REUSA lotes por UNIQUE
     * 4) Mantiene trazabilidad
     */
    public function modificarCompra(int $compraId, array $data, string $motivo): Compra
    {
        return DB::transaction(function () use ($compraId, $data, $motivo) {

            $this->validarDatosCompra($data);

            $compraOriginal = Compra::with(['detalles', 'lotes'])->findOrFail($compraId);

            if (!$compraOriginal->puedeModificarse()) {
                throw new Exception(
                    "La compra #{$compraId} no puede modificarse. " .
                    "Estado: {$compraOriginal->estado}, " .
                    "Reemplazada: " . ($compraOriginal->reemplazada_por ? 'Sí' : 'No')
                );
            }

            foreach ($compraOriginal->lotes as $lote) {
                if ($lote->stock_actual < $lote->stock_inicial) {
                    throw new Exception(
                        "No se puede modificar la compra porque el lote {$lote->numero_lote} " .
                        "ya tiene productos vendidos. Stock inicial: {$lote->stock_inicial}, " .
                        "Stock actual: {$lote->stock_actual}"
                    );
                }
            }

            // Desactivar lotes y revertir inventario
            foreach ($compraOriginal->lotes as $lote) {
                $lote->update(['activo' => false]);

                MovimientoInventario::create([
                    'producto_id'      => $lote->producto_id,
                    'lote_id'          => $lote->id,
                    'user_id'          => Auth::id(),
                    'tipo'             => 'salida',
                    'cantidad'         => -$lote->stock_inicial,
                    'origen'           => 'modificacion_compra',
                    'origen_id'        => $compraOriginal->id,
                    'motivo'           => "Modificación de compra #{$compraOriginal->id}: {$motivo}",
                    'fecha_movimiento' => now(),
                ]);
            }

           
            $nuevaCompra = $this->registrarCompra($data);

            $compraOriginal->update([
                'estado'          => 'anulada',
                'anulado_por'     => Auth::id(),
                'fecha_anulacion' => now(),
                'motivo_anulacion'=> "Modificada - Razón: {$motivo}. Nueva compra: #{$nuevaCompra->id}",
                'reemplazada_por' => $nuevaCompra->id,
            ]);

            $nuevaCompra->update([
                'compra_original_id' => $compraOriginal->id,
            ]);

            return $nuevaCompra->load([
                'detalles.producto',
                'detalles.lote',
                'detalles.presentacion',
                'proveedor',
                'lotes',
                'compraOriginal'
            ]);
        });
    }

    /**
     * Obtener compras recientes
     */
    public function comprasRecientes(int $limite = 10)
    {
        return Compra::with(['proveedor', 'usuario', 'detalles.producto'])
            ->recibidas()
            ->orderBy('fecha', 'desc')
            ->limit($limite)
            ->get();
    }

    /**
     * Obtener total de compras del mes
     */
    public function totalComprasDelMes(): float
    {
        return Compra::whereMonth('fecha', now()->month)
            ->whereYear('fecha', now()->year)
            ->recibidas()
            ->sum('total');
    }

    /**
     * Validar número de lote único para un producto (normalizado)
     */
    public function esNumeroLoteUnico(int $productoId, string $numeroLote): bool
    {
        $numeroLote = $this->normalizarNumeroLote($numeroLote);

        return !Lote::where('producto_id', $productoId)
            ->where('numero_lote', $numeroLote)
            ->exists();
    }

    /**
     * Obtener historial de modificaciones de una compra
     */
    public function historialModificacionesCompra(int $compraId)
    {
        $compra = Compra::findOrFail($compraId);

        return $compra->cadenaModificaciones()->map(function ($c, $index) {
            return [
                'version'          => $index + 1,
                'id'               => $c->id,
                'fecha'            => $c->fecha,
                'total'            => $c->total,
                'estado'           => $c->estado,
                'usuario'          => $c->usuario->name,
                'proveedor'        => $c->proveedor->nombre,
                'es_original'      => is_null($c->compra_original_id),
                'es_activa'        => is_null($c->reemplazada_por) && $c->estado === 'recibida',
                'motivo_anulacion' => $c->motivo_anulacion,
                'total_lotes'      => $c->lotes->count(),
            ];
        });
    }
}