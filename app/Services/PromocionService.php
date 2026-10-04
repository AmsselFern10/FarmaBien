<?php

namespace App\Services;

use App\Models\Promocion;
use App\Models\Producto;
use App\Models\AuditLog;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PromocionService
{
    /**
     * Obtiene el listado paginado de promociones con filtros.
     */
    public function listarPromociones(array $filtros = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Promocion::with([
            'producto:id,nombre,precio_venta,codigo_barra',
            'categoria:id,nombre',
            'laboratorio:id,nombre',
        ]);

        if (!empty($filtros['buscar'])) {
            $query->buscar(trim($filtros['buscar']));
        }

        if (!empty($filtros['tipo'])) {
            $query->porTipo($filtros['tipo']);
        }

        if (!empty($filtros['alcance'])) {
            $query->porAlcance($filtros['alcance']);
        }

        if (!empty($filtros['estado'])) {
            $query->porEstado($filtros['estado']);
        }

        return $query->orderBy('activo', 'desc')
            ->orderBy('fecha_fin', 'asc')
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }

    /**
     * Obtiene estadísticas de promociones para las tarjetas de resumen.
     */
    public function obtenerEstadisticas(): array
    {
        $now = now();
        $total = Promocion::count();
        $vigentes = Promocion::vigentes()->count();
        $programadas = Promocion::where('activo', true)->where('fecha_inicio', '>', $now)->count();
        $vencidas = Promocion::where('fecha_fin', '<', $now)->count();

        return [
            'total'       => $total,
            'vigentes'    => $vigentes,
            'programadas' => $programadas,
            'vencidas'    => $vencidas,
        ];
    }

    /**
     * Registra una nueva promoción comercial invalidando caché de POS.
     */
    public function crearPromocion(array $data): Promocion
    {
        return DB::transaction(function () use ($data) {
            $data['activo'] = $data['activo'] ?? true;
            $data['stock_consumido'] = 0;

            // Limpieza según alcance
            if ($data['alcance'] !== 'producto') $data['producto_id'] = null;
            if ($data['alcance'] !== 'categoria') $data['categoria_id'] = null;
            if ($data['alcance'] !== 'laboratorio') $data['laboratorio_id'] = null;

            $promocion = Promocion::create($data);

            Log::info('Promoción comercial creada', [
                'promocion_id' => $promocion->id,
                'nombre'       => $promocion->nombre,
                'tipo'         => $promocion->tipo,
                'alcance'      => $promocion->alcance,
                'user_id'      => auth()->id(),
            ]);

            AuditLog::log('promociones', 'crear', "Promoción '{$promocion->nombre}' registrada ({$promocion->alcance_descripcion})", [
                'promocion_id' => $promocion->id,
                'tipo'         => $promocion->tipo,
                'valor'        => $promocion->valor,
                'alcance'      => $promocion->alcance,
            ]);

            Cache::forget('promociones_vigentes_pos');

            return $promocion;
        });
    }

    /**
     * Actualiza una promoción existente.
     */
    public function actualizarPromocion(Promocion $promocion, array $data): Promocion
    {
        return DB::transaction(function () use ($promocion, $data) {
            $locked = Promocion::where('id', $promocion->id)->lockForUpdate()->firstOrFail();

            if (isset($data['alcance'])) {
                if ($data['alcance'] !== 'producto') $data['producto_id'] = null;
                if ($data['alcance'] !== 'categoria') $data['categoria_id'] = null;
                if ($data['alcance'] !== 'laboratorio') $data['laboratorio_id'] = null;
            }

            $locked->update($data);

            Log::info('Promoción comercial actualizada', [
                'promocion_id' => $locked->id,
                'nombre'       => $locked->nombre,
                'user_id'      => auth()->id(),
            ]);

            AuditLog::log('promociones', 'editar', "Promoción '{$locked->nombre}' actualizada", [
                'promocion_id' => $locked->id,
                'cambios'      => array_keys($data),
            ]);

            Cache::forget('promociones_vigentes_pos');

            return $locked;
        });
    }

    /**
     * Elimina una promoción comercial.
     */
    public function eliminarPromocion(Promocion $promocion): void
    {
        DB::transaction(function () use ($promocion) {
            $nombre = $promocion->nombre;
            $id = $promocion->id;
            $promocion->delete();

            Log::info('Promoción comercial eliminada', [
                'promocion_id' => $id,
                'nombre'       => $nombre,
                'user_id'      => auth()->id(),
            ]);

            AuditLog::log('promociones', 'eliminar', "Promoción '{$nombre}' eliminada", [
                'promocion_id' => $id,
                'nombre'       => $nombre,
            ]);

            Cache::forget('promociones_vigentes_pos');
        });
    }

    /**
     * Alterna el estado activo/inactivo de una promoción.
     */
    public function cambiarEstado(Promocion $promocion): string
    {
        return DB::transaction(function () use ($promocion) {
            $locked = Promocion::where('id', $promocion->id)->lockForUpdate()->firstOrFail();
            $nuevoEstado = !$locked->activo;
            $locked->update(['activo' => $nuevoEstado]);

            $estadoTexto = $nuevoEstado ? 'activada' : 'desactivada';

            Log::info('Estado de promoción modificado', [
                'promocion_id' => $locked->id,
                'nombre'       => $locked->nombre,
                'nuevo_estado' => $estadoTexto,
                'user_id'      => auth()->id(),
            ]);

            AuditLog::log(
                'promociones',
                $nuevoEstado ? 'activar' : 'desactivar',
                "Promoción '{$locked->nombre}' {$estadoTexto}",
                ['promocion_id' => $locked->id]
            );

            Cache::forget('promociones_vigentes_pos');

            return $estadoTexto;
        });
    }

    /**
     * Obtiene la promoción vigente prioritaria que aplica a un producto.
     * Jerarquía: Producto > Categoría > Laboratorio > General.
     */
    public function obtenerPromocionVigenteParaProducto(Producto $producto): ?Promocion
    {
        $promociones = Cache::remember('promociones_vigentes_pos', 60, function () {
            return Promocion::vigentes()->orderBy('id', 'desc')->get();
        });

        return $promociones->first(function ($p) use ($producto) {
            return $p->alcance === 'producto' && (int)$p->producto_id === (int)$producto->id;
        }) ?? $promociones->first(function ($p) use ($producto) {
            return $p->alcance === 'categoria' && (int)$p->categoria_id === (int)$producto->categoria_id;
        }) ?? $promociones->first(function ($p) use ($producto) {
            return $p->alcance === 'laboratorio' && (int)$p->laboratorio_id === (int)$producto->laboratorio_id;
        }) ?? $promociones->first(function ($p) {
            return $p->alcance === 'general';
        });
    }
}
