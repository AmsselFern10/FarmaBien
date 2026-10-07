<?php

namespace App\Services;

use App\Models\Laboratorio;
use App\Models\AuditLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class LaboratorioService
{
    /**
     * Listar laboratorios con conteo de medicamentos y paginación.
     */
    public function listar(array $filtros = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Laboratorio::withCount('productos');

        if (!empty($filtros['buscar'])) {
            $query->buscar($filtros['buscar']);
        }

        if (!empty($filtros['estado'])) {
            if ($filtros['estado'] === 'activos') {
                $query->where('activo', true);
            } elseif ($filtros['estado'] === 'inactivos') {
                $query->where('activo', false);
            }
        }

        return $query->orderBy('nombre', 'asc')
            ->orderBy('id', 'asc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Crear un nuevo laboratorio farmacéutico.
     */
    public function crear(array $datos, ?int $userId = null): Laboratorio
    {
        return DB::transaction(function () use ($datos, $userId) {
            $laboratorio = Laboratorio::create($datos);

            AuditLog::log('laboratorios', 'crear', "Laboratorio '{$laboratorio->nombre}' creado", [
                'laboratorio_id' => $laboratorio->id,
                'codigo'         => $laboratorio->codigo,
                'user_id'        => $userId ?? auth()->id(),
            ]);

            $this->invalidarCache();

            return $laboratorio;
        });
    }

    /**
     * Actualizar datos de un laboratorio.
     */
    public function actualizar(Laboratorio $laboratorio, array $datos, ?int $userId = null): Laboratorio
    {
        return DB::transaction(function () use ($laboratorio, $datos, $userId) {
            $locked = Laboratorio::where('id', $laboratorio->id)->lockForUpdate()->firstOrFail();
            $locked->update($datos);

            AuditLog::log('laboratorios', 'actualizar', "Laboratorio '{$locked->nombre}' actualizado", [
                'laboratorio_id' => $locked->id,
                'codigo'         => $locked->codigo,
                'user_id'        => $userId ?? auth()->id(),
            ]);

            $this->invalidarCache();

            return $locked;
        });
    }

    /**
     * Alternar estado activo/inactivo con bloqueo y auditoría.
     */
    public function toggleEstado(Laboratorio $laboratorio, ?int $userId = null): string
    {
        return DB::transaction(function () use ($laboratorio, $userId) {
            $locked = Laboratorio::where('id', $laboratorio->id)->lockForUpdate()->firstOrFail();
            $nuevoEstado = !$locked->activo;

            if (!$nuevoEstado && $locked->productos()->where('activo', true)->exists()) {
                throw new \Exception("No se puede desactivar el laboratorio '{$locked->nombre}' porque cuenta con medicamentos activos asociados. Reasigna los productos antes de desactivarlo.");
            }

            $locked->update(['activo' => $nuevoEstado]);

            $accion = $nuevoEstado ? 'activar' : 'desactivar';
            $estadoStr = $nuevoEstado ? 'activado' : 'desactivado';

            AuditLog::log(
                'laboratorios',
                $accion,
                "Laboratorio '{$locked->nombre}' {$estadoStr}",
                ['laboratorio_id' => $locked->id, 'user_id' => $userId ?? auth()->id()]
            );

            $this->invalidarCache();

            return $estadoStr;
        });
    }

    /**
     * Búsqueda AJAX para selectores dinámicos (Componente C / Filtros).
     */
    public function buscarAjax(string $termino, int $limite = 10): Collection
    {
        $limite = min(max($limite, 1), 10);
        $cacheKey = 'laboratorios:buscar_ajax:' . md5(trim($termino)) . ':' . $limite;

        return \App\Facades\RequestCache::remember($cacheKey, function () use ($termino, $limite) {
            $query = Laboratorio::activos();

            if (strlen(trim($termino)) > 0) {
                $query->buscar($termino);
            }

            return $query->orderBy('nombre', 'asc')
                ->limit($limite)
                ->get(['id', 'nombre', 'codigo', 'pais_origen']);
        });
    }

    /**
     * Limpiar cachés dependientes.
     */
    public function invalidarCache(): void
    {
        Cache::forget('catalog_laboratorios_base');
    }
}
