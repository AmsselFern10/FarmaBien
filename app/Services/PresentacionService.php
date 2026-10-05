<?php

namespace App\Services;

use App\Models\PresentacionProducto;
use App\Models\Producto;
use App\Models\AuditLog;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class PresentacionService
{
    /**
     * Obtiene el listado paginado de presentaciones comerciales con filtros.
     */
    public function listarPresentaciones(array $filtros = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = PresentacionProducto::with('producto')
            ->withCount(['detallesVentas', 'detallesCompras']);

        if (!empty($filtros['buscar'])) {
            $query->buscar(trim($filtros['buscar']));
        }

        if (!empty($filtros['producto_id'])) {
            $query->porProducto((int)$filtros['producto_id']);
        }

        if (!empty($filtros['estado'])) {
            $query->where('activo', $filtros['estado'] === 'activos');
        }

        return $query->orderBy('producto_id')->orderBy('orden')->paginate($perPage);
    }

    /**
     * Registra una presentación comercial asegurando la regla de unidad base.
     */
    public function crearPresentacion(array $data): PresentacionProducto
    {
        return DB::transaction(function () use ($data) {
            if (!empty($data['es_unidad_base'])) {
                PresentacionProducto::where('producto_id', $data['producto_id'])
                    ->lockForUpdate()
                    ->update(['es_unidad_base' => false]);
            }

            $presentacion = PresentacionProducto::create($data);

            Log::info('Presentación comercial registrada', [
                'presentacion_id' => $presentacion->id,
                'producto_id'     => $presentacion->producto_id,
                'nombre'          => $presentacion->nombre,
                'unidades'        => $presentacion->unidades_por_presentacion,
                'user_id'         => auth()->id(),
            ]);

            AuditLog::log('presentaciones', 'crear', "Presentación '{$presentacion->nombre}' registrada para producto #{$presentacion->producto_id}", [
                'presentacion_id'           => $presentacion->id,
                'producto_id'               => $presentacion->producto_id,
                'unidades_por_presentacion' => $presentacion->unidades_por_presentacion,
                'precio_venta'              => $presentacion->precio_venta,
            ]);

            return $presentacion;
        });
    }

    /**
     * Actualiza una presentación comercial asegurando la integridad de unidad base.
     */
    public function actualizarPresentacion(PresentacionProducto $presentacion, array $data): PresentacionProducto
    {
        return DB::transaction(function () use ($presentacion, $data) {
            $locked = PresentacionProducto::where('id', $presentacion->id)->lockForUpdate()->firstOrFail();

            if (!empty($data['es_unidad_base'])) {
                PresentacionProducto::where('producto_id', $locked->producto_id)
                    ->where('id', '!=', $locked->id)
                    ->lockForUpdate()
                    ->update(['es_unidad_base' => false]);
            }

            $locked->update($data);

            Log::info('Presentación comercial actualizada', [
                'presentacion_id' => $locked->id,
                'producto_id'     => $locked->producto_id,
                'nombre'          => $locked->nombre,
                'user_id'         => auth()->id(),
            ]);

            AuditLog::log('presentaciones', 'editar', "Presentación '{$locked->nombre}' actualizada (producto #{$locked->producto_id})", [
                'presentacion_id' => $locked->id,
                'producto_id'     => $locked->producto_id,
                'cambios'         => array_keys($data),
            ]);

            return $locked;
        });
    }

    /**
     * Elimina una presentación comercial si no cuenta con historial de movimientos.
     *
     * @throws Exception
     */
    public function eliminarPresentacion(PresentacionProducto $presentacion): void
    {
        DB::transaction(function () use ($presentacion) {
            $locked = PresentacionProducto::where('id', $presentacion->id)->lockForUpdate()->firstOrFail();

            if ($locked->detallesVentas()->exists() || $locked->detallesCompras()->exists()) {
                throw new Exception("No se puede eliminar la presentación '{$locked->nombre}' porque cuenta con registros históricos en ventas o compras. Desactívala para ocultarla.");
            }

            $nombre = $locked->nombre;
            $productoId = $locked->producto_id;
            $locked->delete();

            Log::info('Presentación eliminada exitosamente', [
                'presentacion_id' => $presentacion->id,
                'nombre'          => $nombre,
                'user_id'         => auth()->id(),
            ]);

            AuditLog::log('presentaciones', 'eliminar', "Presentación '{$nombre}' eliminada (producto #{$productoId})", [
                'presentacion_id' => $presentacion->id,
                'producto_id'     => $productoId,
                'nombre'          => $nombre,
            ]);
        });
    }

    /**
     * Alterna el estado activo/inactivo de una presentación comercial.
     */
    public function cambiarEstado(PresentacionProducto $presentacion): string
    {
        return DB::transaction(function () use ($presentacion) {
            $locked = PresentacionProducto::where('id', $presentacion->id)->lockForUpdate()->firstOrFail();
            $nuevoEstado = !$locked->activo;
            $locked->update(['activo' => $nuevoEstado]);

            $estadoTexto = $nuevoEstado ? 'activada' : 'desactivada';

            Log::info('Estado de presentación modificado', [
                'presentacion_id' => $locked->id,
                'nombre'          => $locked->nombre,
                'nuevo_estado'    => $estadoTexto,
                'user_id'         => auth()->id(),
            ]);

            AuditLog::log(
                'presentaciones',
                $nuevoEstado ? 'activar' : 'desactivar',
                "Presentación '{$locked->nombre}' {$estadoTexto}",
                ['presentacion_id' => $locked->id, 'producto_id' => $locked->producto_id]
            );

            return $estadoTexto;
        });
    }

    /**
     * Sincroniza atómicamente el lote de presentaciones asociadas a un producto.
     */
    public function sincronizarPresentacionesDeProducto(Producto $producto, array $presentacionesInput): void
    {
        DB::transaction(function () use ($producto, $presentacionesInput) {
            if (empty($presentacionesInput)) {
                // Si el producto no tiene presentaciones registradas, asegurar la Unidad Base por defecto
                if ($producto->presentaciones()->count() === 0) {
                    PresentacionProducto::create([
                        'producto_id'               => $producto->id,
                        'nombre'                    => 'Unidad Base',
                        'descripcion'               => 'Unidad individual / pastilla / ampolla',
                        'unidades_por_presentacion' => 1,
                        'precio_compra'             => $producto->precio_compra,
                        'precio_venta'              => $producto->precio_venta,
                        'es_unidad_base'            => true,
                        'activo'                    => true,
                        'orden'                     => 1,
                    ]);
                }
                return;
            }

            $processedIds = [];
            $orden = 1;
            $hasBase = false;

            foreach ($presentacionesInput as $p) {
                if (empty($p['nombre'])) continue;

                $esBase = !empty($p['es_unidad_base']) || ((int)($p['unidades_por_presentacion'] ?? 1) === 1 && !$hasBase);
                if ($esBase) $hasBase = true;

                $presentacionData = [
                    'producto_id'               => $producto->id,
                    'nombre'                    => trim($p['nombre']),
                    'descripcion'               => !empty($p['descripcion']) ? trim($p['descripcion']) : null,
                    'unidades_por_presentacion' => max(1, (int)($p['unidades_por_presentacion'] ?? 1)),
                    'precio_compra'             => !empty($p['precio_compra']) ? (float)$p['precio_compra'] : ($esBase ? $producto->precio_compra : null),
                    'precio_venta'              => !empty($p['precio_venta']) ? (float)$p['precio_venta'] : ($esBase ? $producto->precio_venta : null),
                    'codigo_barras'             => !empty($p['codigo_barras']) ? trim($p['codigo_barras']) : null,
                    'es_unidad_base'            => $esBase,
                    'activo'                    => true,
                    'orden'                     => $orden++,
                ];

                if (!empty($p['id'])) {
                    $existing = PresentacionProducto::where('id', $p['id'])
                        ->where('producto_id', $producto->id)
                        ->lockForUpdate()
                        ->first();

                    if ($existing) {
                        $existing->update($presentacionData);
                        $processedIds[] = $existing->id;
                    } else {
                        $newP = PresentacionProducto::create($presentacionData);
                        $processedIds[] = $newP->id;
                    }
                } else {
                    $newP = PresentacionProducto::create($presentacionData);
                    $processedIds[] = $newP->id;
                }
            }

            // Manejo de presentaciones no enviadas: desactivar si tienen historial, eliminar si están limpias
            $presentacionesNoEnviadas = PresentacionProducto::where('producto_id', $producto->id)
                ->whereNotIn('id', $processedIds)
                ->get();

            foreach ($presentacionesNoEnviadas as $pBorrar) {
                $tieneVentas = $pBorrar->detallesVentas()->exists();
                $tieneCompras = $pBorrar->detallesCompras()->exists();

                if ($tieneVentas || $tieneCompras) {
                    $pBorrar->update(['activo' => false]);
                } else {
                    $pBorrar->delete();
                }
            }

            // Garantizar al menos una unidad base
            if (!$hasBase && count($processedIds) > 0) {
                PresentacionProducto::where('id', $processedIds[0])->update(['es_unidad_base' => true]);
            }
        });
    }

    /**
     * Convierte una cantidad de presentaciones a unidades base según el factor multiplicador.
     */
    public function calcularUnidadesBase(int $cantidad, int $unidadesPorPresentacion): int
    {
        return $cantidad * max(1, $unidadesPorPresentacion);
    }
}
