<?php

namespace App\Services;

use App\Models\Proveedor;
use App\Models\AuditLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ProveedorService
{
    /**
     * Listar proveedores con filtros y paginación en servidor.
     */
    public function listar(array $filtros = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Proveedor::withCount('compras');

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
     * Registrar un nuevo proveedor.
     */
    public function crear(array $datos, ?int $userId = null): Proveedor
    {
        return DB::transaction(function () use ($datos, $userId) {
            $proveedor = Proveedor::create($datos);

            AuditLog::log('proveedores', 'crear', "Proveedor '{$proveedor->nombre}' registrado", [
                'proveedor_id' => $proveedor->id,
                'ruc'          => $proveedor->ruc,
                'user_id'      => $userId ?? auth()->id(),
            ]);

            $this->invalidarCache();

            return $proveedor;
        });
    }

    /**
     * Actualizar datos de un proveedor.
     */
    public function actualizar(Proveedor $proveedor, array $datos, ?int $userId = null): Proveedor
    {
        return DB::transaction(function () use ($proveedor, $datos, $userId) {
            $locked = Proveedor::where('id', $proveedor->id)->lockForUpdate()->firstOrFail();
            $locked->update($datos);

            AuditLog::log('proveedores', 'actualizar', "Proveedor '{$locked->nombre}' actualizado", [
                'proveedor_id' => $locked->id,
                'ruc'          => $locked->ruc,
                'user_id'      => $userId ?? auth()->id(),
            ]);

            $this->invalidarCache();

            return $locked;
        });
    }

    /**
     * Alternar estado activo/inactivo con bloqueo y auditoría.
     */
    public function toggleEstado(Proveedor $proveedor, ?int $userId = null): string
    {
        return DB::transaction(function () use ($proveedor, $userId) {
            $locked = Proveedor::where('id', $proveedor->id)->lockForUpdate()->firstOrFail();
            $estadoBool = !$locked->activo;
            $locked->update(['activo' => $estadoBool]);

            $accion = $estadoBool ? 'activar' : 'desactivar';
            $estadoStr = $estadoBool ? 'activado' : 'desactivado';

            AuditLog::log(
                'proveedores',
                $accion,
                "Proveedor '{$locked->nombre}' {$estadoStr}",
                ['proveedor_id' => $locked->id, 'user_id' => $userId ?? auth()->id()]
            );

            $this->invalidarCache();

            return $estadoStr;
        });
    }

    /**
     * Búsqueda AJAX para selectores dinámicos (Componente C / Compras).
     */
    public function buscarAjax(string $termino, int $limite = 10): Collection
    {
        $limite = min(max($limite, 1), 10);
        $cacheKey = 'proveedores:buscar_ajax:' . md5(trim($termino)) . ':' . $limite;

        return \App\Facades\RequestCache::remember($cacheKey, function () use ($termino, $limite) {
            $query = Proveedor::activos();

            if (strlen(trim($termino)) > 0) {
                $query->buscar($termino);
            }

            return $query->orderBy('nombre', 'asc')
                ->limit($limite)
                ->get(['id', 'nombre', 'contacto', 'ciudad', 'ruc', 'telefono'])
                ->map(function ($prov) {
                    return [
                        'id'       => $prov->id,
                        'nombre'   => $prov->nombre,
                        'contacto' => $prov->contacto ?: ($prov->ciudad ?: 'Proveedor Nacional'),
                        'ruc'      => $prov->ruc ? "RUC: {$prov->ruc}" : ($prov->telefono ? "Tel: {$prov->telefono}" : 'S/RUC'),
                    ];
                });
        });
    }

    /**
     * Limpiar cachés dependientes.
     */
    public function invalidarCache(): void
    {
        Cache::forget('catalog_proveedores_base');
    }
}
