<?php

namespace App\Services;

use App\Models\Producto;
use App\Models\AuditLog;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Exception;

class ProductoService
{
    public function __construct(
        protected PresentacionService $presentacionService,
        protected FarmaIaService $iaService
    ) {}

    /**
     * Obtiene el listado paginado de medicamentos aplicando filtros de búsqueda y estado.
     */
    public function listarProductos(array $filtros = [], int $perPage = 12): LengthAwarePaginator
    {
        $query = Producto::with([
            'categoria:id,nombre',
            'laboratorio:id,nombre,codigo',
            'presentacionesActivas:id,producto_id,nombre,unidades_por_presentacion,precio_compra,precio_venta,activo,es_unidad_base'
        ])->withSum(['lotes as stock_total' => function ($q) {
            $q->where('activo', true);
        }], 'stock_actual');

        if (!empty($filtros['buscar'])) {
            $buscar = trim($filtros['buscar']);
            $principiosIa = $this->iaService->obtenerPrincipiosPorSintoma($buscar);
            $query->buscar($buscar, $principiosIa);
        }

        if (!empty($filtros['categoria_id'])) {
            $query->porCategoria((int)$filtros['categoria_id']);
        }

        if (!empty($filtros['laboratorio_id'])) {
            $query->porLaboratorio((int)$filtros['laboratorio_id']);
        }

        if (!empty($filtros['tipo_control'])) {
            $query->porRegimen($filtros['tipo_control']);
        }

        if (!empty($filtros['bajo_stock'])) {
            $query->bajoStock();
        }

        return $query->orderBy('nombre', 'asc')->paginate($perPage);
    }

    /**
     * Registra un nuevo medicamento con sus presentaciones comerciales de forma atómica.
     */
    public function crearProducto(array $data, ?UploadedFile $imagen = null, array $presentaciones = []): Producto
    {
        $uploadedPath = null;

        try {
            return DB::transaction(function () use ($data, $imagen, $presentaciones, &$uploadedPath) {
                if ($imagen) {
                    $uploadedPath = app(ImageOptimizerService::class)->optimizarYGuardarWebp($imagen);
                    $data['imagen'] = $uploadedPath;
                }

                $producto = Producto::create($data);

                // Sincronizar o crear presentaciones comerciales asociadas
                $this->presentacionService->sincronizarPresentacionesDeProducto($producto, $presentaciones);

                Log::info('Producto registrado exitosamente en el catálogo', [
                    'producto_id'  => $producto->id,
                    'nombre'       => $producto->nombre,
                    'codigo_barra' => $producto->codigo_barra,
                    'user_id'      => auth()->id(),
                ]);

                AuditLog::log('productos', 'crear', "Medicamento '{$producto->nombre}' registrado en catálogo", [
                    'producto_id'    => $producto->id,
                    'codigo_barra'   => $producto->codigo_barra,
                    'categoria_id'   => $producto->categoria_id,
                    'laboratorio_id' => $producto->laboratorio_id,
                ]);

                Cache::forget('dashboard_stock_critico_count');

                return $producto;
            });
        } catch (Exception $e) {
            if ($uploadedPath && Storage::disk('public')->exists($uploadedPath)) {
                Storage::disk('public')->delete($uploadedPath);
            }
            throw $e;
        }
    }

    /**
     * Actualiza la ficha y presentaciones de un medicamento existente de forma segura.
     */
    public function actualizarProducto(Producto $producto, array $data, ?UploadedFile $nuevaImagen = null, ?array $presentaciones = null): Producto
    {
        $uploadedPath = null;
        $oldImagePath = $producto->imagen;

        try {
            $productoActualizado = DB::transaction(function () use ($producto, $data, $nuevaImagen, $presentaciones, &$uploadedPath) {
                $locked = Producto::where('id', $producto->id)->lockForUpdate()->firstOrFail();

                if ($nuevaImagen) {
                    $uploadedPath = app(ImageOptimizerService::class)->optimizarYGuardarWebp($nuevaImagen);
                    $data['imagen'] = $uploadedPath;
                }

                $locked->update($data);

                if ($presentaciones !== null) {
                    $this->presentacionService->sincronizarPresentacionesDeProducto($locked, $presentaciones);
                }

                Log::info('Producto actualizado exitosamente', [
                    'producto_id' => $locked->id,
                    'nombre'      => $locked->nombre,
                    'user_id'     => auth()->id(),
                ]);

                AuditLog::log('productos', 'actualizar', "Medicamento '{$locked->nombre}' actualizado", [
                    'producto_id' => $locked->id,
                    'cambios'     => array_keys($data),
                ]);

                Cache::forget('dashboard_stock_critico_count');

                return $locked;
            });

            if ($uploadedPath && $oldImagePath && Storage::disk('private_images')->exists($oldImagePath)) {
                Storage::disk('private_images')->delete($oldImagePath);
            }

            return $productoActualizado;
        } catch (Exception $e) {
            if ($uploadedPath && Storage::disk('private_images')->exists($uploadedPath)) {
                Storage::disk('private_images')->delete($uploadedPath);
            }
            throw $e;
        }
    }

    /**
     * Modifica el estado activo/inactivo de un medicamento.
     */
    public function cambiarEstado(Producto $producto): string
    {
        return DB::transaction(function () use ($producto) {
            $locked = Producto::where('id', $producto->id)->lockForUpdate()->firstOrFail();
            $nuevoEstado = !$locked->activo;
            $locked->update(['activo' => $nuevoEstado]);

            $estadoTexto = $nuevoEstado ? 'activado' : 'desactivado';

            Log::info('Estado de producto modificado', [
                'producto_id'  => $locked->id,
                'nombre'       => $locked->nombre,
                'nuevo_estado' => $estadoTexto,
                'user_id'      => auth()->id(),
            ]);

            AuditLog::log(
                'productos',
                $nuevoEstado ? 'activar' : 'desactivar',
                "Medicamento '{$locked->nombre}' {$estadoTexto}",
                ['producto_id' => $locked->id]
            );

            Cache::forget('dashboard_stock_critico_count');

            return $estadoTexto;
        });
    }

    /**
     * Carga las relaciones completas para la vista detallada de un medicamento.
     */
    public function obtenerFichaCompleta(Producto $producto): Producto
    {
        return $producto->load([
            'categoria',
            'laboratorio',
            'presentaciones',
            'lotes' => function ($q) {
                $q->orderBy('fecha_vencimiento', 'asc');
            }
        ]);
    }

    /**
     * Búsqueda AJAX de medicamentos para selectores dinámicos y componentes.
     */
    public function buscarAjax(string $query, int $limit = 10): array
    {
        $q = trim($query);
        if (strlen($q) < 2) {
            return [];
        }

        return Producto::with(['laboratorio:id,nombre', 'categoria:id,nombre'])
            ->activos()
            ->buscar($q)
            ->limit($limit)
            ->get(['id', 'nombre', 'concentracion', 'forma_farmaceutica', 'principio_activo', 'laboratorio_id', 'categoria_id', 'codigo_barra', 'tipo_control', 'requiere_receta', 'precio_venta'])
            ->map(function ($med) {
                $datoSecundario = $med->principio_activo
                    ? ($med->concentracion ? "{$med->principio_activo} {$med->concentracion}" : $med->principio_activo)
                    : ($med->categoria->nombre ?? 'Medicamento');

                return [
                    'id'               => $med->id,
                    'nombre'           => $med->nombre_completo,
                    'nombre_simple'    => $med->nombre,
                    'dato_secundario'  => $datoSecundario,
                    'principio_activo' => $med->principio_activo,
                    'concentracion'    => $med->concentracion,
                    'laboratorio'      => $med->laboratorio->nombre ?? 'Sin laboratorio',
                    'categoria'        => $med->categoria->nombre ?? 'General',
                    'codigo'           => $med->codigo_barra ?? 'S/C',
                    'codigo_barras'    => $med->codigo_barra ?? 'S/C',
                    'tipo_control'     => $med->tipo_control,
                    'es_controlado'    => $med->esControlado(),
                    'requiere_receta'  => (bool)$med->requiere_receta,
                    'precio_venta'     => (float)$med->precio_venta,
                    'precio_formato'   => number_format((float)$med->precio_venta, 2),
                ];
            })
            ->toArray();
    }
}

