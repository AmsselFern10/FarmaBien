<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\AuditLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ClienteService
{
    /**
     * Listar clientes con filtros avanzados y paginación en servidor.
     */
    public function listar(array $filtros = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Cliente::withCount(['ventas', 'recetas']);

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

        if (!empty($filtros['con_recetas'])) {
            if ($filtros['con_recetas'] === 'si') {
                $query->has('recetas');
            } elseif ($filtros['con_recetas'] === 'no') {
                $query->doesntHave('recetas');
            }
        }

        return $query->orderBy('nombre', 'asc')
            ->orderBy('id', 'asc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Registrar un nuevo cliente / paciente.
     */
    public function crear(array $datos, ?int $userId = null): Cliente
    {
        return DB::transaction(function () use ($datos, $userId) {
            $cliente = Cliente::create($datos);

            AuditLog::log('clientes', 'crear', "Cliente '{$cliente->nombre}' registrado", [
                'cliente_id' => $cliente->id,
                'documento'  => $cliente->documento,
                'user_id'    => $userId ?? auth()->id(),
            ]);

            $this->invalidarCache();

            return $cliente;
        });
    }

    /**
     * Actualizar datos de un cliente existente.
     */
    public function actualizar(Cliente $cliente, array $datos, ?int $userId = null): Cliente
    {
        return DB::transaction(function () use ($cliente, $datos, $userId) {
            $locked = Cliente::where('id', $cliente->id)->lockForUpdate()->firstOrFail();
            $locked->update($datos);

            AuditLog::log('clientes', 'actualizar', "Cliente '{$locked->nombre}' actualizado", [
                'cliente_id' => $locked->id,
                'documento'  => $locked->documento,
                'user_id'    => $userId ?? auth()->id(),
            ]);

            $this->invalidarCache();

            return $locked;
        });
    }

    /**
     * Alternar estado activo/inactivo con bloqueo y auditoría.
     */
    public function toggleEstado(Cliente $cliente, ?int $userId = null): string
    {
        return DB::transaction(function () use ($cliente, $userId) {
            $locked = Cliente::where('id', $cliente->id)->lockForUpdate()->firstOrFail();
            $estadoBool = !$locked->activo;
            $locked->update(['activo' => $estadoBool]);

            $accion = $estadoBool ? 'activar' : 'desactivar';
            $estadoStr = $estadoBool ? 'activado' : 'desactivado';

            AuditLog::log(
                'clientes',
                $accion,
                "Cliente '{$locked->nombre}' {$estadoStr}",
                ['cliente_id' => $locked->id, 'user_id' => $userId ?? auth()->id()]
            );

            $this->invalidarCache();

            return $estadoStr;
        });
    }

    /**
     * Búsqueda AJAX para selectores dinámicos y POS.
     */
    public function buscarAjax(string $termino, int $limite = 10): Collection
    {
        $limite = min(max($limite, 1), 10);
        $cacheKey = 'clientes:buscar_ajax:' . md5(trim($termino)) . ':' . $limite;

        return \App\Facades\RequestCache::remember($cacheKey, function () use ($termino, $limite) {
            $query = Cliente::activos();

            if (strlen(trim($termino)) > 0) {
                $query->buscar($termino);
            }

            return $query->orderBy('nombre', 'asc')
                ->limit($limite)
                ->get(['id', 'nombre', 'documento', 'telefono', 'email', 'direccion']);
        });
    }

    /**
     * Limpiar cachés dependientes.
     */
    public function invalidarCache(): void
    {
        Cache::forget('pos_clientes_init_50');
    }
}
