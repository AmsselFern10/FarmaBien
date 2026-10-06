<?php

namespace App\Services;

use App\Models\Categoria;
use App\Models\AuditLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class CategoriaService
{
    /**
     * Listar categorías con filtros, conteo de productos y paginación.
     */
    public function listar(array $filtros = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Categoria::withCount('productos');

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

        if (!empty($filtros['con_productos'])) {
            if ($filtros['con_productos'] === 'si') {
                $query->has('productos');
            } elseif ($filtros['con_productos'] === 'no') {
                $query->doesntHave('productos');
            }
        }

        return $query->orderBy('nombre', 'asc')
            ->orderBy('id', 'asc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Crear una nueva categoría / grupo terapéutico.
     */
    public function crear(array $datos, ?int $userId = null): Categoria
    {
        return DB::transaction(function () use ($datos, $userId) {
            $categoria = Categoria::create($datos);

            AuditLog::log('categorias', 'crear', "Categoría '{$categoria->nombre}' creada", [
                'categoria_id' => $categoria->id,
                'user_id'      => $userId ?? auth()->id(),
            ]);

            $this->invalidarCache();

            return $categoria;
        });
    }

    /**
     * Actualizar datos de una categoría.
     */
    public function actualizar(Categoria $categoria, array $datos, ?int $userId = null): Categoria
    {
        return DB::transaction(function () use ($categoria, $datos, $userId) {
            $locked = Categoria::where('id', $categoria->id)->lockForUpdate()->firstOrFail();
            $locked->update($datos);

            AuditLog::log('categorias', 'actualizar', "Categoría '{$locked->nombre}' actualizada", [
                'categoria_id' => $locked->id,
                'user_id'      => $userId ?? auth()->id(),
            ]);

            $this->invalidarCache();

            return $locked;
        });
    }

    /**
     * Alternar estado activo/inactivo con bloqueo y auditoría.
     */
    public function toggleEstado(Categoria $categoria, ?int $userId = null): string
    {
        return DB::transaction(function () use ($categoria, $userId) {
            $locked = Categoria::where('id', $categoria->id)->lockForUpdate()->firstOrFail();
            $nuevoEstado = !$locked->activo;

            if (!$nuevoEstado && $locked->productos()->where('activo', true)->exists()) {
                throw new \Exception("No se puede desactivar la categoría '{$locked->nombre}' porque cuenta con medicamentos activos asociados. Reasigna los productos antes de desactivarla.");
            }

            $locked->update(['activo' => $nuevoEstado]);

            $accion = $nuevoEstado ? 'activar' : 'desactivar';
            $estadoStr = $nuevoEstado ? 'activada' : 'desactivada';

            AuditLog::log(
                'categorias',
                $accion,
                "Categoría '{$locked->nombre}' {$estadoStr}",
                ['categoria_id' => $locked->id, 'user_id' => $userId ?? auth()->id()]
            );

            $this->invalidarCache();

            return $estadoStr;
        });
    }

    /**
     * Búsqueda AJAX para selectores dinámicos (Componente C / Tomas / POS).
     */
    public function buscarAjax(string $termino, int $limite = 15): Collection
    {
        $query = Categoria::activas();

        if (strlen(trim($termino)) > 0) {
            $query->buscar($termino);
        }

        return $query->orderBy('nombre', 'asc')
            ->limit($limite)
            ->get(['id', 'nombre', 'descripcion']);
    }

    /**
     * Limpiar cachés dependientes.
     */
    public function invalidarCache(): void
    {
        Cache::forget('catalog_categorias_base');
    }
}
