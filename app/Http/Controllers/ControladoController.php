<?php

namespace App\Http\Controllers;

use App\Models\RegistroVentaControlado;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Exception;

class ControladoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Listado de bitácora oficial y kardex de movimientos de medicamentos controlados.
     */
    public function index(Request $request)
    {
        $query = RegistroVentaControlado::with([
            'producto:id,nombre,principio_activo,tipo_control',
            'venta:id,fecha,total,numero_comprobante',
            'devolucion:id,numero_devolucion,tipo,motivo',
            'compra:id,numero_comprobante,proveedor_id',
            'compra.proveedor:id,nombre',
            'movimientoInventario:id,subtipo,motivo',
            'despachador:id,name',
            'lote:id,numero_lote,fecha_vencimiento',
        ]);

        // Filtro de texto libre
        if ($request->filled('q')) {
            $q = '%' . trim($request->q) . '%';
            $query->where(function ($sub) use ($q) {
                $sub->where('paciente_nombre', 'like', $q)
                    ->orWhere('paciente_cedula', 'like', $q)
                    ->orWhere('medico_nombre', 'like', $q)
                    ->orWhere('medico_num_registro', 'like', $q)
                    ->orWhere('motivo_omision', 'like', $q)
                    ->orWhereHas('producto', function ($pq) use ($q) {
                        $pq->where('nombre', 'like', $q)
                           ->orWhere('principio_activo', 'like', $q);
                    })
                    ->orWhereHas('lote', function ($lq) use ($q) {
                        $lq->where('numero_lote', 'like', $q);
                    });
            });
        }

        // Filtro por tipo de movimiento / operación
        if ($request->filled('tipo_movimiento')) {
            $query->tipoMovimiento($request->tipo_movimiento);
        }

        // Filtro por modalidad de despacho (receta vs omisión en ventas)
        if ($request->filled('tipo_despacho')) {
            if ($request->tipo_despacho === 'omision') {
                $query->conOmision();
            } elseif ($request->tipo_despacho === 'con_receta') {
                $query->conReceta();
            }
        }

        // Filtro por rango de fechas
        if ($request->filled('desde')) {
            $query->whereDate('created_at', '>=', $request->desde);
        }

        if ($request->filled('hasta')) {
            $query->whereDate('created_at', '<=', $request->hasta);
        }

        // Filtro por producto específico
        if ($request->filled('producto_id')) {
            $query->where('producto_id', $request->producto_id);
        }

        // Métricas de resumen para KPI cards
        $kpiBaseQuery = clone $query;
        $totalMovimientos = (clone $kpiBaseQuery)->count();
        $totalEntradas = (clone $kpiBaseQuery)->whereIn('tipo_movimiento', [
            RegistroVentaControlado::TIPO_DEVOLUCION_STOCK,
            RegistroVentaControlado::TIPO_AJUSTE_INGRESO,
            RegistroVentaControlado::TIPO_ANULACION_VENTA,
            RegistroVentaControlado::TIPO_COMPRA,
        ])->sum('cantidad');

        $totalSalidas = (clone $kpiBaseQuery)->whereIn('tipo_movimiento', [
            RegistroVentaControlado::TIPO_VENTA,
            RegistroVentaControlado::TIPO_DEVOLUCION_MERMA,
            RegistroVentaControlado::TIPO_AJUSTE_EGRESO,
        ])->sum('cantidad');

        $totalMermas = (clone $kpiBaseQuery)->whereIn('tipo_movimiento', [
            RegistroVentaControlado::TIPO_DEVOLUCION_MERMA,
            RegistroVentaControlado::TIPO_AJUSTE_EGRESO,
        ])->sum('cantidad');

        $registros = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        // Para selector de productos controlados
        $productosControlados = Producto::controlados()
            ->select('id', 'nombre')
            ->orderBy('nombre')
            ->get();

        return view('controlados.index', compact(
            'registros',
            'productosControlados',
            'totalMovimientos',
            'totalEntradas',
            'totalSalidas',
            'totalMermas'
        ));
    }

    /**
     * Ver detalle completo y auditable de un movimiento de medicamento controlado.
     */
    public function show(RegistroVentaControlado $registro)
    {
        $registro->load([
            'producto.laboratorio',
            'venta.usuario',
            'venta.recetas',
            'venta.detalles.recetaDetalle.receta',
            'venta.cliente',
            'devolucion.usuario',
            'compra.proveedor',
            'movimientoInventario',
            'despachador',
            'lote',
        ]);

        return view('controlados.show', compact('registro'));
    }

    /**
     * Libro de control imprimible para inspección MINSA / SILAIS.
     */
    public function libroControl(Request $request)
    {
        $desde = $request->desde ?? now()->startOfMonth()->toDateString();
        $hasta = $request->hasta ?? now()->toDateString();

        $query = RegistroVentaControlado::with([
            'producto:id,nombre,principio_activo,concentracion,tipo_control',
            'venta:id,fecha,numero_comprobante',
            'devolucion:id,numero_devolucion',
            'compra:id,numero_comprobante',
            'despachador:id,name',
            'lote:id,numero_lote',
        ])
        ->whereDate('created_at', '>=', $desde)
        ->whereDate('created_at', '<=', $hasta);

        if ($request->filled('tipo_movimiento')) {
            $query->tipoMovimiento($request->tipo_movimiento);
        }

        if ($request->filled('tipo_despacho')) {
            if ($request->tipo_despacho === 'omision') {
                $query->conOmision();
            } elseif ($request->tipo_despacho === 'con_receta') {
                $query->conReceta();
            }
        }

        if ($request->filled('producto_id')) {
            $query->where('producto_id', $request->producto_id);
        }

        if ($request->filled('q')) {
            $q = '%' . trim($request->q) . '%';
            $query->where(function ($sub) use ($q) {
                $sub->where('paciente_nombre', 'like', $q)
                    ->orWhere('paciente_cedula', 'like', $q)
                    ->orWhere('medico_nombre', 'like', $q)
                    ->orWhere('medico_num_registro', 'like', $q)
                    ->orWhere('motivo_omision', 'like', $q)
                    ->orWhereHas('producto', function ($pq) use ($q) {
                        $pq->where('nombre', 'like', $q)
                           ->orWhere('principio_activo', 'like', $q);
                    })
                    ->orWhereHas('lote', function ($lq) use ($q) {
                        $lq->where('numero_lote', 'like', $q);
                    });
            });
        }

        $registros = $query->orderBy('created_at')->get();

        $farmacia = [
            'nombre'    => config('app.name', 'FarmaBien'),
            'direccion' => 'Local Central FarmaBien',
            'telefono'  => '+505 2200-0000',
        ];

        return view('controlados.libro', compact('registros', 'desde', 'hasta', 'farmacia'));
    }

    /**
     * Exportar reporte consolidado de controlados a formato compatible con Excel / SILAIS.
     */
    public function exportarExcel(Request $request)
    {
        $desde = $request->desde ?? now()->startOfMonth()->toDateString();
        $hasta = $request->hasta ?? now()->toDateString();

        $query = RegistroVentaControlado::with([
            'producto.laboratorio',
            'venta.usuario',
            'devolucion',
            'compra.proveedor',
            'despachador',
            'lote',
        ])
        ->whereDate('created_at', '>=', $desde)
        ->whereDate('created_at', '<=', $hasta);

        if ($request->filled('tipo_movimiento')) {
            $query->tipoMovimiento($request->tipo_movimiento);
        }

        if ($request->filled('tipo_despacho')) {
            if ($request->tipo_despacho === 'omision') {
                $query->conOmision();
            } elseif ($request->tipo_despacho === 'con_receta') {
                $query->conReceta();
            }
        }

        if ($request->filled('producto_id')) {
            $query->where('producto_id', $request->producto_id);
        }

        if ($request->filled('q')) {
            $q = '%' . trim($request->q) . '%';
            $query->where(function ($sub) use ($q) {
                $sub->where('paciente_nombre', 'like', $q)
                    ->orWhere('paciente_cedula', 'like', $q)
                    ->orWhere('medico_nombre', 'like', $q)
                    ->orWhere('medico_num_registro', 'like', $q)
                    ->orWhere('motivo_omision', 'like', $q)
                    ->orWhereHas('producto', function ($pq) use ($q) {
                        $pq->where('nombre', 'like', $q)
                           ->orWhere('principio_activo', 'like', $q);
                    })
                    ->orWhereHas('lote', function ($lq) use ($q) {
                        $lq->where('numero_lote', 'like', $q);
                    });
            });
        }

        $registros = $query->orderBy('created_at')->get();

        $filename = "Libro_Controlados_MINSA_{$desde}_al_{$hasta}.xls";

        $content = view('controlados.excel.libro', compact('registros', 'desde', 'hasta'))->render();

        return response($content, 200, [
            'Content-Type'        => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ]);
    }

    /**
     * Subir fotografía o evidencia escaneada de la receta física o justificante.
     */
    public function subirEvidencia(Request $request, RegistroVentaControlado $registro)
    {
        $request->validate([
            'foto_receta' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf,webp', 'max:5120'],
        ]);

        try {
            if ($registro->ruta_foto_receta && Storage::disk('local')->exists($registro->ruta_foto_receta)) {
                Storage::disk('local')->delete($registro->ruta_foto_receta);
            }

            $path = $request->file('foto_receta')->store('recetas_controlados', 'local');
            $registro->update(['ruta_foto_receta' => $path]);

            AuditLog::log('controlados', 'subir_evidencia', "Evidencia adjunta al registro de controlado #{$registro->id}", [
                'registro_id' => $registro->id,
                'user_id' => auth()->id(),
            ]);

            return back()->with('success', 'Documento de evidencia adjuntado exitosamente.');
        } catch (Exception $e) {
            return back()->with('error', 'Error al subir la evidencia: ' . $e->getMessage());
        }
    }

    /**
     * Visualizar evidencia fotográfica o justificante digital de forma segura.
     */
    public function verEvidencia(RegistroVentaControlado $registro)
    {
        if (empty($registro->ruta_foto_receta)) {
            abort(404, 'No hay archivo de evidencia adjunto a este registro.');
        }

        $path = null;
        if (Storage::disk('local')->exists($registro->ruta_foto_receta)) {
            $path = Storage::disk('local')->path($registro->ruta_foto_receta);
        } elseif (Storage::disk('public')->exists($registro->ruta_foto_receta)) {
            $path = Storage::disk('public')->path($registro->ruta_foto_receta);
        }

        if (!$path || !file_exists($path)) {
            abort(404, 'El archivo físico no fue encontrado en el servidor.');
        }

        $mimeType = mime_content_type($path) ?: 'application/octet-stream';
        $fileName = basename($registro->ruta_foto_receta);

        return response()->file($path, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => "inline; filename=\"{$fileName}\"",
            'Cache-Control' => 'private, no-cache, no-store, must-revalidate',
        ]);
    }
}
