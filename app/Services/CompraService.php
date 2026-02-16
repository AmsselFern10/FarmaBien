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
use Illuminate\Support\Carbon;
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
     * Resuelve la fecha de la compra asegurando que guarde hora.
     *
     * - Si viene solo fecha (Y-m-d), se agrega hora actual (o la hora de $fallback si se provee).
     * - Si viene datetime (incluye HH:MM), se respeta tal cual.
     */
    protected function resolverFecha(?string $fechaInput, $fallback = null): Carbon
    {
        $now = now();

        if ($fallback && !($fallback instanceof Carbon)) {
            $fallback = Carbon::parse($fallback);
        }

        if (!$fechaInput) {
            return $now;
        }

        $fechaInput = trim($fechaInput);

        $tieneHora = (bool) preg_match('/\d{2}:\d{2}/', $fechaInput);

        if ($tieneHora) {
            return Carbon::parse($fechaInput);
        }

        $date = Carbon::parse($fechaInput)->toDateString();
        $time = ($fallback ?: $now)->format('H:i:s');

        return Carbon::parse($date . ' ' . $time);
    }

    /**
     * Compra "raíz" para inventario:
     * - Si esta compra es una versión (compra_original_id), los movimientos de entrada están ligados a la raíz.
     * - Si no, la raíz es su propio id.
     */
    /**
 * Compra "raíz" para cadena de modificaciones.
 * Nota: NO se usa para reversas de inventario; para eso se resuelve con obtenerCompraInventarioId().
 */
protected function obtenerCompraRootId(Compra $compra): int
{
    return (int) ($compra->compra_original_id ?: $compra->id);
}

/**
 * Resuelve contra cuál compra están registradas las ENTRADAS de inventario.
 *
 * Casos:
 * - Compra "real" (registrarCompra / modificarCompra): tiene movimientos (origen=compra, origen_id=esta compra).
 * - Versión "solo datos": NO crea movimientos; sus entradas viven en la compra anterior/original.
 */
protected function obtenerCompraInventarioId(Compra $compra): int
{
    $hasEntradasPropias = MovimientoInventario::query()
        ->where('origen', 'compra')
        ->where('origen_id', (int) $compra->id)
        ->where('tipo', 'entrada')
        ->exists();

    if ($hasEntradasPropias) {
        return (int) $compra->id;
    }

    $candidateId = (int) ($compra->compra_original_id ?: $compra->id);

    // Si no hay compra_original_id, no hay más que buscar.
    if ($candidateId === (int) $compra->id) {
        return $candidateId;
    }

    // Subimos por la cadena compra_original_id hasta encontrar una compra con entradas.
    $seen = [(int) $compra->id];
    $currentId = $candidateId;

    while ($currentId && !in_array($currentId, $seen, true)) {
        $seen[] = $currentId;

        $has = MovimientoInventario::query()
            ->where('origen', 'compra')
            ->where('origen_id', $currentId)
            ->where('tipo', 'entrada')
            ->exists();

        if ($has) {
            return $currentId;
        }

        $next = Compra::query()->select('id', 'compra_original_id')->find($currentId);

        if (!$next || !$next->compra_original_id) {
            break;
        }

        $currentId = (int) $next->compra_original_id;
    }

    return $candidateId;
}


    /**
     * Entradas de inventario asociadas a la compra raíz.
     * (Append-only: estos movimientos NO se editan; para corregir se generan reversas.)
     */
    protected function obtenerEntradasPorCompraRoot(int $compraInventarioId)
    {
        return MovimientoInventario::query()
            ->where('origen', 'compra')
            ->where('origen_id', $compraInventarioId)
            ->where('tipo', 'entrada')
            ->orderBy('id')
            ->get();
    }

    /**
     * Valida que exista stock suficiente para revertir todas las entradas de una compra raíz.
     * Bloquea los lotes para evitar condiciones de carrera y devuelve el mapa de lotes lockeados.
     *
     * @throws Exception
     */
    protected function validarStockParaRevertirEntradas($entradas)
    {
        $requeridoPorLote = [];

        foreach ($entradas as $m) {
            $loteId = (int) $m->lote_id;
            $requeridoPorLote[$loteId] = ($requeridoPorLote[$loteId] ?? 0) + abs((int) $m->cantidad);
        }

        if (empty($requeridoPorLote)) {
            return collect();
        }

        $lotes = Lote::query()
            ->whereIn('id', array_keys($requeridoPorLote))
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        foreach ($requeridoPorLote as $loteId => $qty) {
            /** @var Lote|null $lote */
            $lote = $lotes->get($loteId);

            if (!$lote) {
                throw new Exception("No se encontró el lote #{$loteId} asociado a la compra.");
            }

            if ((int) $lote->stock_actual < (int) $qty) {
                throw new Exception(
                    "No se puede continuar: el lote {$lote->numero_lote} no tiene stock suficiente para revertir la compra. " .
                    "Requerido: {$qty}, Stock actual: {$lote->stock_actual}."
                );
            }
        }

        return $lotes;
    }

    /**
     * Crea reversas (salidas) por cada entrada. Mantiene trazabilidad con reversa_de_id.
     *
     * @throws Exception
     */
    protected function revertirEntradas($entradas, string $origen, int $origenId, string $motivo): void
    {
        foreach ($entradas as $entrada) {
            MovimientoInventario::create([
                'producto_id'      => $entrada->producto_id, // se forzará con el lote
                'lote_id'          => $entrada->lote_id,
                'user_id'          => Auth::id(),
                'tipo'             => 'salida',
                'cantidad'         => abs((int) $entrada->cantidad),
                'reversa_de_id'    => $entrada->id,
                'origen'           => $origen,
                'origen_id'        => $origenId,
                'motivo'           => trim($motivo ?: 'Reversa de movimientos de compra'),
                'fecha_movimiento' => now(),
            ]);
        }
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
                'fecha' => $this->resolverFecha($data['fecha'] ?? null),

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

        // Nota: el precio_unitario se interpreta como PRECIO POR UNIDAD BASE.
        // Si el usuario ingresa precio por presentación, eso debe convertirse en el frontend (o en una capa dedicada),
        // para no contaminar la lógica de stock/costos.
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

        // Costo unitario NETO para lote/movimiento (evita costo inflado por descuentos)
        $costoUnitarioNeto = round($subtotalNeto / $cantidadUnidadesBase, 6);

        // 6) Lote (blindado por producto_id + numero_lote)
        $numeroLote = $this->normalizarNumeroLote($item['numero_lote'] ?? '');
        if ($numeroLote === '') {
            throw new Exception("El número de lote es obligatorio para el producto: {$producto->nombre}");
        }

        if (empty($item['fecha_vencimiento'])) {
            throw new Exception("La fecha de vencimiento es obligatoria para el lote {$numeroLote} del producto: {$producto->nombre}");
        }

        $fechaVenc = Carbon::parse($item['fecha_vencimiento'])->toDateString();

        /** @var Lote|null $lote */
        $lote = Lote::where('producto_id', $producto->id)
            ->where('numero_lote', $numeroLote)
            ->first();

        if ($lote) {
            // Evita incoherencias sanitarias: mismo número de lote debe mantener vencimiento.
            $vencExistente = $lote->fecha_vencimiento ? $lote->fecha_vencimiento->toDateString() : null;

            if ($vencExistente && $vencExistente !== $fechaVenc) {
                throw new Exception(
                    "Inconsistencia de vencimiento: el lote {$numeroLote} del producto {$producto->nombre} " .
                    "ya existe con vencimiento {$vencExistente}. No se permite cambiarlo a {$fechaVenc}."
                );
            }

            $lote->update([
                'activo'            => true,
                'compra_id'         => $lote->compra_id ?: $compra->id,
                'proveedor_id'      => $compra->proveedor_id, // referencia operativa (la trazabilidad real está en Compra/Movimientos)
                'fecha_vencimiento' => $vencExistente ? $lote->fecha_vencimiento : $fechaVenc,
                // stock_actual se actualiza por Movimientos (no aquí)
                // stock_inicial: acumulado de entradas para referencia (sin afectar cálculos)
                'stock_inicial'     => (int)($lote->stock_inicial ?? 0) + $cantidadUnidadesBase,
                'precio_compra'     => $costoUnitarioNeto, // costo unitario neto de referencia del lote
            ]);
        } else {
            $lote = Lote::create([
                'producto_id'       => $producto->id,
                'compra_id'         => $compra->id,              // compra de creación del lote
                'proveedor_id'      => $compra->proveedor_id,
                'numero_lote'       => $numeroLote,
                'fecha_vencimiento' => $fechaVenc,
                'fecha_ingreso'     => now(),
                'stock_inicial'     => $cantidadUnidadesBase,    // acumulado de entradas (referencia)
                'stock_actual'      => 0,                        // se actualiza por Movimientos
                'precio_compra'     => $costoUnitarioNeto,
                'estado'            => 'activo',
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

            'precio_unitario'           => $precioUnitario,   // BRUTO por unidad base
            'subtotal_bruto'            => $subtotalBruto,
            'descuento_porcentaje'      => $descuentoPct,
            'descuento_monto'           => $descuentoMonto,
            'subtotal'                  => $subtotalNeto,     // NETO
        ]);

        // 8) Movimiento inventario (ENTRADA en unidades base)
        MovimientoInventario::create([
            'producto_id'      => $producto->id, // el modelo forzará consistencia con el lote
            'lote_id'          => $lote->id,
            'user_id'          => Auth::id(),
            'tipo'             => 'entrada',
            'cantidad'         => $cantidadUnidadesBase,
            'costo_unitario'   => $costoUnitarioNeto,
            'origen'           => 'compra',
            'origen_id'        => $compra->id,
            'motivo'           => $presentacionId
                ? "Compra #{$compra->id} - {$cantidadPresentaciones} {$tipoPresentacion}(s) x {$unidadesPorPresentacion} = {$cantidadUnidadesBase} unidades - Lote {$lote->numero_lote}"
                : "Compra #{$compra->id} - {$cantidadUnidadesBase} unidades - Lote {$lote->numero_lote}",
            'fecha_movimiento' => now(),
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

            $compra = Compra::with(['detalles'])->findOrFail($compraId);

            if (!$compra->puedeAnularse()) {
                throw new Exception("La compra #{$compraId} ya está anulada.");
            }

            $compraInventarioId = $this->obtenerCompraInventarioId($compra);
            $entradas = $this->obtenerEntradasPorCompraRoot($compraInventarioId);

            if ($entradas->isEmpty()) {
                throw new Exception("No se encontraron entradas de inventario para la compra de inventario #{$compraInventarioId}.");
            }

            // Validar que se pueda revertir el impacto de inventario
            $this->validarStockParaRevertirEntradas($entradas);

            // Revertir entradas (append-only)
            $this->revertirEntradas(
                $entradas,
                'anulacion_compra',
                $compra->id,
                "Anulación de compra #{$compra->id}: " . ($motivo ?: 'Sin motivo')
            );

            $compra->update([
                'estado'           => 'anulada',
                'anulado_por'      => Auth::id(),
                'fecha_anulacion'  => now(),
                'motivo_anulacion' => $motivo ?: 'Compra anulada',
            ]);

            return $compra->fresh()->load([
                'detalles.producto',
                'detalles.lote',
                'detalles.presentacion',
                'proveedor',
            ]);
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

            // Compra vigente (puede ser raíz o una versión "solo datos")
            $compraVigente = Compra::with(['detalles'])->findOrFail($compraId);

            if (!$compraVigente->puedeModificarse()) {
                throw new Exception(
                    "La compra #{$compraId} no puede modificarse. " .
                    "Estado: {$compraVigente->estado}, " .
                    "Reemplazada: " . ($compraVigente->reemplazada_por ? 'Sí' : 'No')
                );
            }

            // Revertimos inventario contra la compra raíz (donde se registraron las entradas).
            $compraInventarioId = $this->obtenerCompraInventarioId($compraVigente);
            $entradas = $this->obtenerEntradasPorCompraRoot($compraInventarioId);

            if ($entradas->isEmpty()) {
                throw new Exception("No se encontraron entradas de inventario para la compra de inventario #{$compraInventarioId}.");
            }

            // 1) Validar stock suficiente (sin depender de stock_inicial)
            $this->validarStockParaRevertirEntradas($entradas);

            // 2) Revertir entradas (append-only) con reversa_de_id
            $this->revertirEntradas(
                $entradas,
                'modificacion_compra',
                $compraVigente->id,
                "Modificación de compra #{$compraVigente->id}: " . ($motivo ?: 'Sin motivo')
            );

            // Mantener la hora (si el formulario solo envía fecha)
            $data['fecha'] = $this->resolverFecha($data['fecha'] ?? null, $compraVigente->fecha)->toDateTimeString();

            // 3) Registrar nueva compra (nueva recepción con nuevos lotes/movimientos)
            $nuevaCompra = $this->registrarCompra($data);

            // 4) Anular la compra vigente (la versión que se está reemplazando)
            $compraVigente->update([
                'estado'           => 'anulada',
                'anulado_por'      => Auth::id(),
                'fecha_anulacion'  => now(),
                'motivo_anulacion' => "Modificada - Razón: {$motivo}. Nueva compra: #{$nuevaCompra->id}",
                'reemplazada_por'  => $nuevaCompra->id,
            ]);

            // 5) Vincular trazabilidad (la nueva compra sabe de cuál proviene)
            $nuevaCompra->update([
                'compra_original_id' => ($compraVigente->compra_original_id ?: $compraVigente->id),
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
 * Versiona SOLO datos de cabecera (sin tocar inventario).
 *
 * Qué hace:
 * - Crea una nueva compra (versión) con los cambios de cabecera.
 * - Copia los detalles (líneas) tal como estaban, sin recalcular stock.
 * - Re-asigna los lotes existentes a la nueva compra SIN cambiar stock_inicial/stock_actual.
 * - NO crea movimientos de inventario nuevos.
 * - Mantiene trazabilidad: anula la compra anterior y la enlaza con reemplazada_por / compra_original_id.
 *
 * Importante:
 * - Esto permite corregir proveedor/fecha/observaciones/descuento aun cuando ya hubo ventas desde los lotes.
 */
public function versionarCompraSoloDatos(int $compraId, array $data, string $motivo): Compra
{
    return DB::transaction(function () use ($compraId, $data, $motivo) {
        $compraAnterior = Compra::with(['detalles', 'detalles.lote', 'lotes'])->findOrFail($compraId);

        if (!$compraAnterior->puedeModificarse()) {
            throw new Exception("No se puede modificar esta compra.");
        }

        $proveedorId = (int)($data['proveedor_id'] ?? $compraAnterior->proveedor_id);
        if (!$proveedorId) {
            throw new Exception("El proveedor es obligatorio.");
        }

        $fecha = $this->resolverFecha($data['fecha'] ?? null, $compraAnterior->fecha)->toDateTimeString();

        $descuentoGlobalPct = round((float)($data['descuento'] ?? $compraAnterior->descuento_porcentaje ?? 0), 2);
        if ($descuentoGlobalPct < 0 || $descuentoGlobalPct > 100) {
            throw new Exception("El descuento debe estar entre 0 y 100.");
        }

        $observaciones = $data['observaciones'] ?? $compraAnterior->observaciones;

        // 1) Crear nueva compra (versión)
        $nuevaCompra = Compra::create([
            'proveedor_id' => $proveedorId,
            'user_id' => Auth::id(),
            'estado' => 'recibida',
            'fecha' => $fecha,
            'subtotal_bruto' => 0,
            'descuento_porcentaje' => $descuentoGlobalPct,
            'descuento_monto_total' => 0,
            'total' => 0,
            'observaciones' => $observaciones,
        ]);

        // 2) Copiar detalles (sin tocar stock)
        $subtotalBruto = 0;
        $descuentoLineas = 0;
        $subtotalNeto = 0;

        foreach ($compraAnterior->detalles as $d) {
            DetalleCompra::create([
                'compra_id' => $nuevaCompra->id,
                'producto_id' => $d->producto_id,
                'lote_id' => $d->lote_id,
                'presentacion_id' => $d->presentacion_id,
                'tipo_presentacion' => $d->tipo_presentacion,
                'unidades_por_presentacion' => $d->unidades_por_presentacion,
                'cantidad_presentaciones' => $d->cantidad_presentaciones,
                'cantidad_unidades_base' => $d->cantidad_unidades_base,
                'precio_unitario' => $d->precio_unitario,
                'subtotal_bruto' => $d->subtotal_bruto,
                'descuento_porcentaje' => $d->descuento_porcentaje,
                'descuento_monto' => $d->descuento_monto,
                'subtotal' => $d->subtotal,
            ]);

            $subtotalBruto += (float)($d->subtotal_bruto ?? 0);
            $descuentoLineas += (float)($d->descuento_monto ?? 0);
            $subtotalNeto += (float)($d->subtotal ?? 0);
        }

        $subtotalBruto = round($subtotalBruto, 2);
        $descuentoLineas = round($descuentoLineas, 2);
        $subtotalNeto = round($subtotalNeto, 2);

        $descuentoGlobalMonto = round($subtotalNeto * ($descuentoGlobalPct / 100), 2);
        $totalFinal = round($subtotalNeto - $descuentoGlobalMonto, 2);

        $nuevaCompra->update([
            'subtotal_bruto' => $subtotalBruto,
            'descuento_porcentaje' => $descuentoGlobalPct,
            'descuento_monto_total' => round($descuentoLineas + $descuentoGlobalMonto, 2),
            'total' => $totalFinal,
        ]);

        // 3) Re-asignar lotes al documento activo (sin tocar stock ni movimientos)
        // - Esto mantiene la operatividad (anular / validar vendidos) sobre la versión vigente
        // - La versión anterior queda resguardada (sus detalles siguen apuntando a los mismos lotes)
        foreach ($compraAnterior->lotes as $lote) {
            $lote->update([
                'compra_id'    => $nuevaCompra->id,
                'proveedor_id' => $proveedorId,
                'activo'       => true,
            ]);
        }


        // 4) Anular la compra anterior y enlazar versión
        $compraAnterior->update([
            'estado'          => 'anulada',
            'anulado_por'     => Auth::id(),
            'fecha_anulacion' => now(),
            'motivo_anulacion'=> trim("Modificada (solo datos) - Razón: " . ($motivo ?: 'Sin motivo') . ". Nueva compra: #{$nuevaCompra->id}"),
            'reemplazada_por' => $nuevaCompra->id,
        ]);

        $nuevaCompra->update([
            'compra_original_id' => ($compraAnterior->compra_original_id ?: $compraAnterior->id),
        ]);
return $nuevaCompra->load(['proveedor', 'detalles.producto', 'detalles.presentacion', 'detalles.lote', 'lotes']);
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
     * Obtener historial de modificaciones de una compra (cadena completa).
     *
     * - Evita N+1 (precarga proveedor/usuario/detalles).
     * - total_lotes se calcula desde detalles (no desde $compra->lotes), para no perder trazabilidad
     *   cuando los lotes cambian de compra_id por versionado.
     */
    public function historialModificacionesCompra(int $compraId)
    {
        $compra = Compra::findOrFail($compraId);

        $cadena = $compra->cadenaModificaciones();

        $ids = $cadena->pluck('id')->values()->all();

        $compras = Compra::query()
            ->with([
                'usuario:id,name',
                'proveedor:id,nombre',
                'detalles:id,compra_id,lote_id',
                // Se deja lotes por si algún blade lo necesita; no se usa para total_lotes.
                'lotes:id,compra_id',
            ])
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        return $cadena->map(function ($c, $index) use ($compras) {
            /** @var Compra $cc */
            $cc = $compras->get($c->id, $c);

            $lotesUnicos = $cc->detalles
                ? $cc->detalles->pluck('lote_id')->filter()->unique()->count()
                : 0;

            return [
                'version'          => $index + 1,
                'id'               => $cc->id,
                'fecha'            => $cc->fecha,
                'total'            => $cc->total,
                'estado'           => $cc->estado,
                'usuario'          => optional($cc->usuario)->name,
                'proveedor'        => optional($cc->proveedor)->nombre,
                'es_original'      => is_null($cc->compra_original_id),
                'es_activa'        => is_null($cc->reemplazada_por) && $cc->estado === 'recibida',
                'motivo_anulacion' => $cc->motivo_anulacion,
                'total_lotes'      => $lotesUnicos,
            ];
        })->values();
    }
}
