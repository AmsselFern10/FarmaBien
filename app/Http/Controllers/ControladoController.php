<?php

namespace App\Http\Controllers;

use App\Models\RegistroVentaControlado;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ControladoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Listado de registros de despacho de medicamentos controlados.
     */
    public function index(Request $request)
    {
        $query = RegistroVentaControlado::with([
            'producto:id,nombre,principio_activo,nivel_controlado',
            'venta:id,fecha,total',
            'despachador:id,name',
        ]);

        // Filtros
        if ($request->filled('q')) {
            $q = '%' . $request->q . '%';
            $query->where(function ($sub) use ($q) {
                $sub->where('paciente_nombre', 'like', $q)
                    ->orWhere('paciente_cedula', 'like', $q)
                    ->orWhere('medico_nombre', 'like', $q);
            });
        }

        if ($request->filled('nivel')) {
            $query->where('nivel_controlado', $request->nivel);
        }

        if ($request->filled('desde')) {
            $query->whereDate('created_at', '>=', $request->desde);
        }

        if ($request->filled('hasta')) {
            $query->whereDate('created_at', '<=', $request->hasta);
        }

        if ($request->filled('producto_id')) {
            $query->where('producto_id', $request->producto_id);
        }

        $registros = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        // Para filtro de productos controlados
        $productosControlados = Producto::where('nivel_controlado', '>', 0)
            ->select('id', 'nombre')
            ->orderBy('nombre')
            ->get();

        return view('controlados.index', compact('registros', 'productosControlados'));
    }

    /**
     * Libro de control imprimible para inspección MINSA.
     */
    public function libroControl(Request $request)
    {
        $desde = $request->desde ?? now()->startOfMonth()->toDateString();
        $hasta = $request->hasta ?? now()->toDateString();
        $nivel = $request->nivel;

        $query = RegistroVentaControlado::with([
            'producto:id,nombre,principio_activo,concentracion,nivel_controlado',
            'venta:id,fecha',
            'despachador:id,name',
            'lote:id,numero_lote',
        ])
        ->whereDate('created_at', '>=', $desde)
        ->whereDate('created_at', '<=', $hasta);

        if ($nivel) {
            $query->where('nivel_controlado', $nivel);
        }

        $registros = $query->orderBy('created_at')->get();

        $farmacia = [
            'nombre'    => config('app.name', 'FarmaBien'),
            'direccion' => '',
            'telefono'  => '',
        ];

        return view('controlados.libro', compact('registros', 'desde', 'hasta', 'nivel', 'farmacia'));
    }

    /**
     * Registrar datos de paciente/médico al completar una venta con controlados.
     * Llamado vía AJAX desde el POS.
     */
    public function store(Request $request)
    {
        $request->validate([
            'venta_id'              => 'required|exists:ventas,id',
            'registros'             => 'required|array|min:1',
            'registros.*.producto_id'      => 'required|exists:productos,id',
            'registros.*.lote_id'          => 'nullable|exists:lotes,id',
            'registros.*.nivel_controlado' => 'required|integer|min:1|max:3',
            'registros.*.paciente_nombre'  => 'required|string|max:150',
            'registros.*.paciente_cedula'  => 'nullable|string|max:30',
            'registros.*.paciente_edad'    => 'nullable|integer|min:0|max:120',
            'registros.*.medico_nombre'    => 'required|string|max:150',
            'registros.*.medico_cedula'    => 'nullable|string|max:30',
            'registros.*.medico_num_registro' => 'nullable|string|max:60',
            'registros.*.diagnostico'      => 'nullable|string|max:255',
            'registros.*.cantidad'         => 'required|numeric|min:0.01',
            'registros.*.unidad'           => 'nullable|string|max:50',
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->registros as $item) {
                RegistroVentaControlado::create([
                    'venta_id'            => $request->venta_id,
                    'producto_id'         => $item['producto_id'],
                    'lote_id'             => $item['lote_id'] ?? null,
                    'nivel_controlado'    => $item['nivel_controlado'],
                    'paciente_nombre'     => $item['paciente_nombre'],
                    'paciente_cedula'     => $item['paciente_cedula'] ?? null,
                    'paciente_edad'       => $item['paciente_edad'] ?? null,
                    'medico_nombre'       => $item['medico_nombre'],
                    'medico_cedula'       => $item['medico_cedula'] ?? null,
                    'medico_num_registro' => $item['medico_num_registro'] ?? null,
                    'diagnostico'         => $item['diagnostico'] ?? null,
                    'cantidad'            => $item['cantidad'],
                    'unidad'              => $item['unidad'] ?? 'unidad',
                    'user_id'             => auth()->id(),
                ]);
            }

            DB::commit();

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
