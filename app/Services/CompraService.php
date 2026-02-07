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
    public function registrarCompra(array $data): Compra
{
    return DB::transaction(function () use ($data) {

        // 0. Validar datos de presentaciones
        $this->validarDatosCompra($data);

        $descuento = round((float)($data['descuento'] ?? 0), 2);
        $observaciones = $data['observaciones'] ?? null;

        if ($descuento < 0 || $descuento > 100) {
            throw new Exception('El descuento debe estar entre 0 y 100.');
        }

        // 1. Crear la compra
        $compra = Compra::create([
            'proveedor_id' => $data['proveedor_id'],
            'user_id' => Auth::id(),
            'total' => 0,
            'descuento' => $descuento,
            'observaciones' => $observaciones,
            'estado' => 'recibida',
            'fecha' => $data['fecha'] ?? now(),
        ]);

        $subtotal = 0;

        // 2. Procesar cada producto
        foreach ($data['productos'] as $item) {
            $subtotal += $this->procesarDetalleCompra($compra, $item);
        }

        $subtotal = round($subtotal, 2);
        $montoDescuento = round($subtotal* ($descuento / 100), 2);
        $total= round($subtotal - $montoDescuento, 2);

     

        // 5. Actualizar total (y descuento por si vino vacío o normalizado)
        $compra->update([
            'total' => $total,
            'descuento' => $descuento,
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
    protected function procesarDetalleCompra(Compra $compra, array $item): float
    {
        // 1. Validar que el producto exista
        $producto = Producto::findOrFail($item['producto_id']);

        // 2. Datos del item
        $presentacionId        = $item['presentacion_id'] ?? null;
        $cantidadPresentaciones = $item['cantidad_presentaciones'] ?? $item['cantidad'] ?? 1;

        // Precio por UNIDAD BASE
        $precioUnitario = $item['precio_unitario'] ?? $producto->precio_compra;

        // 3. Determinar unidades por presentación y tipo
        if ($presentacionId) {
            $presentacion = PresentacionProducto::findOrFail($presentacionId);
            $unidadesPorPresentacion = (int) $presentacion->unidades_por_presentacion;
            $tipoPresentacion = $presentacion->nombre;
        } else {
            $unidadesPorPresentacion = 1;
            $tipoPresentacion = 'Unidad';
        }

        // 4. Calcular totales
        $cantidadUnidadesBase = (int) $cantidadPresentaciones * (int) $unidadesPorPresentacion;
        $precioPresentacion   = round((float) $precioUnitario * (int) $unidadesPorPresentacion, 2);
        $subtotal             = round($precioPresentacion * (float) $cantidadPresentaciones, 2);

        // 5. Lote (BLINDADO contra duplicados por UNIQUE producto_id + numero_lote)
        $numeroLote = $this->normalizarNumeroLote($item['numero_lote'] ?? '');

        if ($numeroLote === '') {
            throw new Exception("El número de lote es obligatorio para el producto: {$producto->nombre}");
        }

        if (empty($item['fecha_vencimiento'])) {
            throw new Exception("La fecha de vencimiento es obligatoria para el lote {$numeroLote} del producto: {$producto->nombre}");
        }

        // Buscar lote existente aunque esté inactivo (NO crear duplicado)
        $lote = Lote::where('producto_id', $producto->id)
            ->where('numero_lote', $numeroLote)
            ->first();

        if ($lote) {
            // Reutilizar/reactivar lote existente
            $lote->update([
                'activo'           => true,
                'compra_id'        => $compra->id,
                'proveedor_id'     => $compra->proveedor_id,
                'fecha_vencimiento'=> $item['fecha_vencimiento'],
                'stock_inicial'    => $cantidadUnidadesBase,
                'precio_compra'    => $precioUnitario,
            ]);
        } else {
            // Crear solo si no existe
            $lote = Lote::create([
                'producto_id'       => $producto->id,
                'compra_id'         => $compra->id,
                'proveedor_id'      => $compra->proveedor_id,
                'numero_lote'       => $numeroLote,
                'fecha_vencimiento' => $item['fecha_vencimiento'],
                'stock_inicial'     => $cantidadUnidadesBase,
                'precio_compra'     => $precioUnitario,
                'activo'            => true,
            ]);
        }

        // 6. Crear detalle de compra
        $detalle = DetalleCompra::create([
            'compra_id'                => $compra->id,
            'producto_id'              => $producto->id,
            'lote_id'                  => $lote->id,
            'presentacion_id'          => $presentacionId,
            'tipo_presentacion'        => $tipoPresentacion,
            'unidades_por_presentacion'=> $unidadesPorPresentacion,
            'cantidad_presentaciones'  => $cantidadPresentaciones,
            // cantidad_unidades_base se calcula automáticamente en la BD (según tu comentario)
            'cantidad_legacy'          => $cantidadUnidadesBase, // compatibilidad
            'precio_unitario'          => $precioUnitario,
            'subtotal'                 => $subtotal,
        ]);

        // 7. Movimiento inventario (ENTRADA en unidades base)
        MovimientoInventario::create([
            'producto_id'       => $producto->id,
            'lote_id'           => $lote->id,
            'user_id'           => Auth::id(),
            'tipo'              => 'entrada',
            'cantidad'          => $cantidadUnidadesBase,
            'origen'            => 'compra',
            'origen_id'         => $compra->id,
            'motivo'            => $presentacionId
                ? "Compra #{$compra->id} - {$cantidadPresentaciones} {$tipoPresentacion}(s) x {$unidadesPorPresentacion} = {$cantidadUnidadesBase} unidades - Lote {$lote->numero_lote}"
                : "Compra #{$compra->id} - {$cantidadUnidadesBase} unidades - Lote {$lote->numero_lote}",
            'fecha_movimiento'  => now(),
        ]);

        return $subtotal;
    }

    /**
     * Validar datos de compra
     *
     * @throws Exception
     */
    protected function validarDatosCompra(array $data): void
    {
        if (!isset($data['productos']) || !is_array($data['productos']) || count($data['productos']) < 1) {
            throw new Exception('Debe proporcionar al menos un producto.');
        }

        foreach ($data['productos'] as $index => $item) {
            // producto_id obligatorio
            if (empty($item['producto_id'])) {
                throw new Exception("Falta el producto en el ítem #{$index}.");
            }

            // Lote obligatorio
            $numeroLote = $this->normalizarNumeroLote($item['numero_lote'] ?? '');
            if ($numeroLote === '') {
                throw new Exception("El número de lote en el producto #{$index} es obligatorio.");
            }

            // Vencimiento obligatorio (ajústalo si en tu negocio no siempre aplica)
            if (empty($item['fecha_vencimiento'])) {
                throw new Exception("La fecha de vencimiento en el producto #{$index} es obligatoria.");
            }

            // Validar presentación si viene
            if (!empty($item['presentacion_id'])) {
                $presentacion = PresentacionProducto::find($item['presentacion_id']);

                if (!$presentacion) {
                    throw new Exception("La presentación seleccionada en el producto #{$index} no existe.");
                }

                if (!$presentacion->activo) {
                    throw new Exception("La presentación '{$presentacion->nombre}' en el producto #{$index} no está activa.");
                }

                if ((int) $presentacion->producto_id !== (int) $item['producto_id']) {
                    throw new Exception("La presentación seleccionada no corresponde al producto en el ítem #{$index}.");
                }
            }

            // Validar cantidad de presentaciones
            $cantidad = $item['cantidad_presentaciones'] ?? $item['cantidad'] ?? 0;
            if ((float) $cantidad < 1) {
                throw new Exception("La cantidad en el producto #{$index} debe ser mayor a 0.");
            }

            // Validar precio unitario (unidad base)
            $precioUnitario = $item['precio_unitario'] ?? 0;
            if ((float) $precioUnitario < 0) {
                throw new Exception("El precio unitario en el producto #{$index} no puede ser negativo.");
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