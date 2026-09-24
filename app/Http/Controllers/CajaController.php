<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use App\Models\SesionCaja;
use App\Models\MovimientoCaja;
use App\Models\User;
use App\Models\AuditLog;
use App\Services\CajaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Exception;

class CajaController extends Controller
{
    protected CajaService $cajaService;

    public function __construct(CajaService $cajaService)
    {
        $this->cajaService = $cajaService;
        $this->middleware('permission:ver cajas')->only(['index', 'show', 'sesiones', 'ticketArqueo']);
        $this->middleware('permission:crear cajas')->only(['store']);
        $this->middleware('permission:editar cajas')->only(['update']);
        $this->middleware('permission:desactivar cajas')->only(['destroy']);
        $this->middleware('permission:abrir caja')->only(['abrir']);
        $this->middleware('permission:cerrar caja')->only(['cerrar']);
        $this->middleware('permission:registrar movimientos caja')->only(['storeMovimiento']);
    }

    /**
     * Dashboard Multicaja: visualización en tiempo real y panel de control
     */
    public function index()
    {
        $cajas = Caja::with(['sesionActiva.usuario'])->orderBy('nombre')->get();
        $user = auth()->user();
        $sesionUsuario = $this->cajaService->obtenerSesionActivaUsuario($user);

        // KPIs Generales
        $totalCajas = $cajas->count();
        $cajasAbiertas = $cajas->filter(fn($c) => $c->sesionActiva !== null)->count();
        $cajasCerradas = $totalCajas - $cajasAbiertas;

        $efectivoTotalCajas = 0;
        foreach ($cajas as $caja) {
            if ($caja->sesionActiva) {
                $this->cajaService->recalcularTotales($caja->sesionActiva);
                $efectivoTotalCajas += (float) $caja->sesionActiva->monto_esperado_efectivo;
            }
        }

        $cajasDisponiblesParaAbrir = $cajas->filter(fn($c) => $c->activo && $c->sesionActiva === null);

        return view('cajas.index', compact(
            'cajas',
            'sesionUsuario',
            'totalCajas',
            'cajasAbiertas',
            'cajasCerradas',
            'efectivoTotalCajas',
            'cajasDisponiblesParaAbrir'
        ));
    }

    /**
     * Crear una nueva caja física o virtual
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre'      => ['required', 'string', 'max:100'],
            'codigo'      => ['required', 'string', 'max:50', 'unique:cajas,codigo'],
            'ubicacion'   => ['nullable', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ], [
            'nombre.required' => 'El nombre de la caja es obligatorio.',
            'codigo.required' => 'El código identificador es obligatorio.',
            'codigo.unique'   => 'Ya existe una caja registrada con este código.',
        ]);

        try {
            $caja = Caja::create([
                'nombre'      => trim($validated['nombre']),
                'codigo'      => trim($validated['codigo']),
                'ubicacion'   => !empty($validated['ubicacion']) ? trim($validated['ubicacion']) : null,
                'descripcion' => !empty($validated['descripcion']) ? trim($validated['descripcion']) : null,
                'activo'      => true,
            ]);

            AuditLog::log('cajas', 'crear', "Caja '{$caja->nombre}' ({$caja->codigo}) creada", [
                'caja_id' => $caja->id,
            ]);

            return redirect()->route('cajas.index')
                ->with('success', "Caja '{$caja->nombre}' registrada exitosamente.");
        } catch (QueryException $e) {
            Log::error("Error al registrar caja: " . $e->getMessage());
            return back()->withInput()->with('error', 'Error en la base de datos al registrar la caja.');
        }
    }

    /**
     * Actualizar datos de una caja
     */
    public function update(Request $request, Caja $caja)
    {
        $validated = $request->validate([
            'nombre'      => ['required', 'string', 'max:100'],
            'codigo'      => ['required', 'string', 'max:50', 'unique:cajas,codigo,' . $caja->id],
            'ubicacion'   => ['nullable', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'activo'      => ['boolean'],
        ]);

        try {
            $caja->update([
                'nombre'      => trim($validated['nombre']),
                'codigo'      => trim($validated['codigo']),
                'ubicacion'   => !empty($validated['ubicacion']) ? trim($validated['ubicacion']) : null,
                'descripcion' => !empty($validated['descripcion']) ? trim($validated['descripcion']) : null,
                'activo'      => $request->boolean('activo'),
            ]);

            AuditLog::log('cajas', 'actualizar', "Caja '{$caja->nombre}' actualizada", [
                'caja_id' => $caja->id,
            ]);

            return redirect()->route('cajas.index')
                ->with('success', "Caja '{$caja->nombre}' actualizada correctamente.");
        } catch (QueryException $e) {
            Log::error("Error al actualizar caja #{$caja->id}: " . $e->getMessage());
            return back()->withInput()->with('error', 'Error en la base de datos al actualizar la caja.');
        }
    }

    /**
     * Desactivar o eliminar una caja
     */
    public function destroy(Caja $caja)
    {
        if ($caja->estaAbierta()) {
            return back()->with('error', "No se puede desactivar la caja '{$caja->nombre}' porque tiene una sesión de turno abierta actualmente.");
        }

        $caja->update(['activo' => !$caja->activo]);
        $estado = $caja->activo ? 'activada' : 'desactivada';

        AuditLog::log('cajas', $caja->activo ? 'activar' : 'desactivar', "Caja '{$caja->nombre}' {$estado}", [
            'caja_id' => $caja->id,
        ]);

        return redirect()->route('cajas.index')
            ->with('success', "Caja '{$caja->nombre}' {$estado} correctamente.");
    }

    /**
     * Apertura de turno de caja
     */
    public function abrir(Request $request, Caja $caja)
    {
        $request->validate([
            'monto_inicial'          => ['required', 'numeric', 'min:0'],
            'observaciones_apertura' => ['nullable', 'string', 'max:255'],
        ], [
            'monto_inicial.required' => 'Debes ingresar el fondo o saldo inicial de apertura.',
            'monto_inicial.min'      => 'El fondo inicial no puede ser negativo.',
        ]);

        try {
            $sesion = $this->cajaService->abrirCaja(
                $caja,
                auth()->user(),
                (float) $request->input('monto_inicial'),
                $request->input('observaciones_apertura')
            );

            AuditLog::log('cajas', 'abrir_turno', "Apertura de turno en '{$caja->nombre}' con fondo de $" . number_format($sesion->monto_inicial, 2), [
                'sesion_id' => $sesion->id,
                'caja_id' => $caja->id,
                'monto_inicial' => $sesion->monto_inicial,
            ]);

            return redirect()->route('cajas.show', $sesion)
                ->with('success', "Turno de caja abierto correctamente en '{$caja->nombre}' con un fondo de $" . number_format($sesion->monto_inicial, 2));
        } catch (QueryException $e) {
            Log::error("Error de DB al abrir turno en caja #{$caja->id}: " . $e->getMessage());
            return back()->with('error', 'Error en la base de datos al abrir el turno de caja.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Detalle y panel de control de una sesión / arqueo
     */
    public function show(SesionCaja $sesion)
    {
        $sesion->load(['caja', 'usuario', 'usuarioCierre', 'movimientos.usuario', 'ventas.cliente']);

        if ($sesion->estaAbierta()) {
            $this->cajaService->recalcularTotales($sesion);
            $sesion->refresh();
        }

        $ventas = $sesion->ventas()->with('cliente')->latest('fecha')->paginate(15);
        $movimientos = $sesion->movimientos()->with('usuario')->latest('created_at')->get();

        return view('cajas.show', compact('sesion', 'ventas', 'movimientos'));
    }

    /**
     * Registrar movimiento manual (Ingreso / Egreso)
     */
    public function storeMovimiento(Request $request, SesionCaja $sesion)
    {
        $request->validate([
            'tipo'                   => ['required', 'in:ingreso,egreso'],
            'monto'                  => ['required', 'numeric', 'min:0.01'],
            'concepto'               => ['required', 'string', 'max:255'],
            'comprobante_referencia' => ['nullable', 'string', 'max:100'],
        ], [
            'monto.min'         => 'El monto debe ser mayor a 0.',
            'concepto.required' => 'Debes ingresar un motivo o concepto para el movimiento.',
        ]);

        try {
            $mov = $this->cajaService->registrarMovimiento(
                $sesion,
                auth()->user(),
                $request->input('tipo'),
                (float) $request->input('monto'),
                $request->input('concepto'),
                $request->input('comprobante_referencia')
            );

            $tipoTexto = $request->input('tipo') === 'ingreso' ? 'Ingreso manual' : 'Egreso / Retiro';

            AuditLog::log('cajas', 'movimiento_manual', "{$tipoTexto} de $" . number_format($mov->monto, 2) . " en sesión #{$sesion->id}: {$mov->concepto}", [
                'sesion_id' => $sesion->id,
                'tipo' => $mov->tipo,
                'monto' => $mov->monto,
                'concepto' => $mov->concepto,
            ]);

            return back()->with('success', "{$tipoTexto} registrado exitosamente.");
        } catch (QueryException $e) {
            Log::error("Error de DB al registrar movimiento de caja: " . $e->getMessage());
            return back()->with('error', 'Error en la base de datos al registrar el movimiento.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Arqueo y cierre formal de caja
     */
    public function cerrar(Request $request, SesionCaja $sesion)
    {
        $request->validate([
            'monto_final_efectivo' => ['required', 'numeric', 'min:0'],
            'observaciones_cierre' => ['nullable', 'string', 'max:500'],
        ], [
            'monto_final_efectivo.required' => 'Debes declarar el efectivo físico total recontado.',
            'monto_final_efectivo.min'      => 'El monto final no puede ser negativo.',
        ]);

        try {
            $sesionCerrada = $this->cajaService->cerrarCaja(
                $sesion,
                auth()->user(),
                (float) $request->input('monto_final_efectivo'),
                $request->input('observaciones_cierre')
            );

            $dif = (float) $sesionCerrada->diferencia_efectivo;
            $msgDif = $dif == 0 
                ? "Cuadre exacto sin diferencias." 
                : ($dif > 0 ? "Sobrante de +$" . number_format($dif, 2) : "Faltante de -$" . number_format(abs($dif), 2));

            AuditLog::log('cajas', 'cerrar_turno', "Cierre y arqueo de sesión #{$sesionCerrada->id}. {$msgDif}", [
                'sesion_id' => $sesionCerrada->id,
                'monto_final_efectivo' => $sesionCerrada->monto_final_efectivo,
                'diferencia' => $dif,
            ]);

            return redirect()->route('cajas.show', $sesionCerrada)
                ->with('success', "Caja cerrada formalmente. {$msgDif}");
        } catch (QueryException $e) {
            Log::error("Error de DB al cerrar sesión de caja #{$sesion->id}: " . $e->getMessage());
            return back()->with('error', 'Error en la base de datos al cerrar la caja.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Historial de sesiones y arqueos de caja
     */
    public function sesiones(Request $request)
    {
        $fechaDesde = $request->input('fecha_desde', now()->startOfMonth()->toDateString());
        $fechaHasta = $request->input('fecha_hasta', now()->endOfMonth()->toDateString());
        $cajaId = $request->input('caja_id');
        $cajeroId = $request->input('cajero_id');
        $estado = $request->input('estado');

        $query = SesionCaja::with(['caja', 'usuario', 'usuarioCierre'])
            ->whereDate('fecha_apertura', '>=', $fechaDesde)
            ->whereDate('fecha_apertura', '<=', $fechaHasta);

        if (!empty($cajaId)) {
            $query->where('caja_id', $cajaId);
        }

        if (!empty($cajeroId)) {
            $query->where('user_id', $cajeroId);
        }

        if (!empty($estado)) {
            $query->where('estado', $estado);
        }

        $sesiones = $query->orderByDesc('fecha_apertura')->paginate(20)->withQueryString();
        $cajas = Caja::orderBy('nombre')->get(['id', 'nombre', 'codigo']);
        $cajeros = User::whereHas('sesionesCaja')->orderBy('name')->get(['id', 'name']);

        return view('cajas.sesiones', compact(
            'sesiones',
            'cajas',
            'cajeros',
            'fechaDesde',
            'fechaHasta',
            'cajaId',
            'cajeroId',
            'estado'
        ));
    }

    /**
     * Comprobante / Ticket de arqueo y cierre de caja imprimible
     */
    public function ticketArqueo(SesionCaja $sesion)
    {
        $sesion->load(['caja', 'usuario', 'usuarioCierre', 'movimientos']);
        return view('cajas.ticket-arqueo', compact('sesion'));
    }
}
