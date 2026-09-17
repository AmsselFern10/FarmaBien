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
     * Registrar una compra completa con soporte para presentaciones fraccionadas y Kardex auditado
     * 
     * @param array $data
     * @return Compra
     * @throws Exception
     */
    public function registrarCompra(array $data): Compra
    {
        return DB::transaction(function () use ($data) {
            
            $this->validarDatosCompra($data);
            
            // 1. Crear cabecera de la compra
            $compra = Compra::create([
                'proveedor_id' => $data['proveedor_id'],
                'user_id' => Auth::id() ?? 1,
                'numero_comprobante' => $data['numero_comprobante'] ?? null,
                'subtotal' => 0,
                'impuesto' => 0,
                'total' => 0,
                'estado' => 'recibida',
                'fecha' => $data['fecha'] ?? now(),
            ]);

            $totalAcumulado = 0;

            // 2. Procesar cada producto adquirido
            foreach ($data['productos'] as $item) {
                $subtotalItem = $this->procesarDetalleCompra($compra, $item);
                $totalAcumulado += $subtotalItem;
            }

            // 3. Actualizar importes totales
            $compra->update([
                'subtotal' => $totalAcumulado,
                'total' => $totalAcumulado,
            ]);

            return $compra->load([
                'detalles.producto.laboratorio',
                'detalles.lote',
                'detalles.presentacion',
                'proveedor',
                'lotes'
            ]);
        });
    }

    /**
     * Procesar un ítem de detalle de compra
     * 
     * @param Compra $compra
     * @param array $item
     * @return float
     * @throws Exception
     */
    protected function procesarDetalleCompra(Compra $compra, array $item): float
    {
        $producto = Producto::findOrFail($item['producto_id']);
        
        $presentacionId = $item['presentacion_id'] ?? null;
        $cantidadPresentaciones = (int) ($item['cantidad_presentaciones'] ?? $item['cantidad'] ?? 1);
        $precioPresentacion = (float) $item['precio_unitario'];
        
        // Determinar factor de conversión
        if ($presentacionId) {
            $presentacion = PresentacionProducto::where('producto_id', $producto->id)
                ->where('id', $presentacionId)
                ->firstOrFail();
            $unidadesPorPresentacion = $presentacion->unidades_por_presentacion;
            $tipoPresentacion = $presentacion->nombre;
        } else {
            $unidadesPorPresentacion = 1;
            $tipoPresentacion = 'Unidad Base';
        }

        // Cálculos de unidades base y costos
        $cantidadUnidadesBase = $cantidadPresentaciones * $unidadesPorPresentacion;
        $subtotal = round($cantidadPresentaciones * $precioPresentacion, 2);
        $costoUnitarioBase = round($precioPresentacion / $unidadesPorPresentacion, 4);

        // Validar número de lote y fecha de vencimiento
        $numeroLote = trim($item['numero_lote']);
        $fechaVencimiento = $item['fecha_vencimiento'];

        // 1. Crear el Lote
        $lote = Lote::create([
            'producto_id' => $producto->id,
            'compra_id' => $compra->id,
            'proveedor_id' => $compra->proveedor_id,
            'numero_lote' => $numeroLote,
            'fecha_vencimiento' => $fechaVencimiento,
            'stock_inicial' => $cantidadUnidadesBase,
            'stock_actual' => $cantidadUnidadesBase,
            'precio_compra' => $costoUnitarioBase,
            'activo' => true,
        ]);

        // 2. Crear Detalle de Compra
        DetalleCompra::create([
            'compra_id' => $compra->id,
            'producto_id' => $producto->id,
            'lote_id' => $lote->id,
            'presentacion_id' => $presentacionId,
            'tipo_presentacion' => $tipoPresentacion,
            'unidades_por_presentacion' => $unidadesPorPresentacion,
            'cantidad_presentaciones' => $cantidadPresentaciones,
            'cantidad_unidades_base' => $cantidadUnidadesBase,
            'precio_unitario' => $precioPresentacion,
            'subtotal' => $subtotal,
        ]);

        // 3. Registrar en Kardex Auditoría (ENTRADA por COMPRA)
        MovimientoInventario::create([
            'producto_id' => $producto->id,
            'lote_id' => $lote->id,
            'user_id' => Auth::id() ?? 1,
            'tipo' => 'entrada',
            'subtipo' => 'compra',
            'cantidad' => $cantidadUnidadesBase,
            'stock_anterior' => 0,
            'stock_posterior' => $cantidadUnidadesBase,
            'costo_unitario' => $costoUnitarioBase,
            'costo_total' => $subtotal,
            'origen' => 'compra',
            'origen_id' => $compra->id,
            'motivo' => "Ingreso por Compra #{$compra->id} (Doc: {$compra->numero_comprobante}) - Lote {$lote->numero_lote}",
            'fecha_movimiento' => now(),
        ]);

        // 4. Actualizar precio de compra de referencia en el producto
        $producto->update(['precio_compra' => $costoUnitarioBase]);

        return $subtotal;
    }

    /**
     * Validar integridad de datos de compra
     * 
     * @param array $data
     * @throws Exception
     */
    protected function validarDatosCompra(array $data): void
    {
        if (!isset($data['productos']) || !is_array($data['productos']) || empty($data['productos'])) {
            throw new Exception('Debe incluir al menos un producto en la compra.');
        }

        foreach ($data['productos'] as $index => $item) {
            if (empty($item['producto_id'])) {
                throw new Exception("El ítem #{$index} no tiene un producto seleccionado.");
            }
            if (empty($item['numero_lote'])) {
                throw new Exception("El ítem #{$index} requiere especificar el número de lote.");
            }
            if (empty($item['fecha_vencimiento'])) {
                throw new Exception("El ítem #{$index} requiere la fecha de vencimiento del lote.");
            }
            $cantidad = (int) ($item['cantidad_presentaciones'] ?? $item['cantidad'] ?? 0);
            if ($cantidad <= 0) {
                throw new Exception("La cantidad en el ítem #{$index} debe ser mayor a 0.");
            }
            $precio = (float) ($item['precio_unitario'] ?? 0);
            if ($precio < 0) {
                throw new Exception("El precio en el ítem #{$index} no puede ser negativo.");
            }
        }
    }

    /**
     * Anular una compra (validando que los lotes no hayan sido vendidos)
     * 
     * @param int $compraId
     * @param string $motivo
     * @return Compra
     * @throws Exception
     */
    public function anularCompra(int $compraId, string $motivo): Compra
    {
        return DB::transaction(function () use ($compraId, $motivo) {
            
            $compra = Compra::with(['detalles', 'lotes'])
                ->where('id', $compraId)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$compra->puedeAnularse()) {
                throw new Exception("La compra #{$compraId} no puede ser anulada porque ya está anulada o fue modificada.");
            }

            // Validar que ningún lote tenga ventas
            foreach ($compra->lotes as $lote) {
                $loteBloqueado = Lote::where('id', $lote->id)->lockForUpdate()->first();
                if ($loteBloqueado->stock_actual < $loteBloqueado->stock_inicial) {
                    $vendidas = $loteBloqueado->stock_inicial - $loteBloqueado->stock_actual;
                    throw new Exception(
                        "No se puede anular la compra: el lote '{$loteBloqueado->numero_lote}' ya tiene {$vendidas} unidades vendidas."
                    );
                }
            }

            // Proceder con la anulación y registro en Kardex
            foreach ($compra->lotes as $lote) {
                $stockAnterior = $lote->stock_actual;
                $costoUnitario = (float) $lote->precio_compra;
                $costoTotal = round($stockAnterior * $costoUnitario, 2);

                $lote->stock_actual = 0;
                $lote->activo = false;
                $lote->save();

                MovimientoInventario::create([
                    'producto_id' => $lote->producto_id,
                    'lote_id' => $lote->id,
                    'user_id' => Auth::id() ?? 1,
                    'tipo' => 'salida',
                    'subtipo' => 'anulacion_compra',
                    'cantidad' => -$stockAnterior,
                    'stock_anterior' => $stockAnterior,
                    'stock_posterior' => 0,
                    'costo_unitario' => $costoUnitario,
                    'costo_total' => $costoTotal,
                    'origen' => 'anulacion_compra',
                    'origen_id' => $compra->id,
                    'motivo' => "Anulación de compra #{$compra->id}: {$motivo}",
                    'fecha_movimiento' => now(),
                ]);
            }

            $compra->update([
                'estado' => 'anulada',
                'anulado_por' => Auth::id() ?? 1,
                'fecha_anulacion' => now(),
                'motivo_anulacion' => $motivo,
            ]);

            return $compra->fresh(['detalles', 'lotes', 'anuladoPor']);
        });
    }

    /**
     * Modificar una compra existente con trazabilidad completa
     * 
     * @param int $compraId
     * @param array $data
     * @param string $motivo
     * @return Compra
     * @throws Exception
     */
    public function modificarCompra(int $compraId, array $data, string $motivo): Compra
    {
        return DB::transaction(function () use ($compraId, $data, $motivo) {
            
            $compraOriginal = Compra::with('lotes')->where('id', $compraId)->lockForUpdate()->firstOrFail();

            if (!$compraOriginal->puedeModificarse()) {
                throw new Exception("La compra #{$compraId} no puede modificarse en su estado actual.");
            }

            // Anular compra original
            $this->anularCompra($compraId, "Modificación - {$motivo}");

            // Crear nueva compra
            $nuevaCompra = $this->registrarCompra($data);

            // Establecer enlaces de trazabilidad
            $compraOriginal->update(['reemplazada_por' => $nuevaCompra->id]);
            $nuevaCompra->update(['compra_original_id' => $compraOriginal->id]);

            return $nuevaCompra->load(['detalles', 'proveedor', 'lotes', 'compraOriginal']);
        });
    }
}
