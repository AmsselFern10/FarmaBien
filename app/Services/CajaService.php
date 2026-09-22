<?php

namespace App\Services;

use App\Models\Caja;
use App\Models\SesionCaja;
use App\Models\MovimientoCaja;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;
use Exception;

class CajaService
{
    /**
     * Obtener la sesión activa de un usuario / cajero
     */
    public function obtenerSesionActivaUsuario(User $user): ?SesionCaja
    {
        return SesionCaja::with(['caja', 'usuario'])
            ->where('user_id', $user->id)
            ->where('estado', 'abierta')
            ->latest('fecha_apertura')
            ->first();
    }

    /**
     * Obtener la sesión activa de una caja física
     */
    public function obtenerSesionActivaCaja(Caja $caja): ?SesionCaja
    {
        return SesionCaja::with(['caja', 'usuario'])
            ->where('caja_id', $caja->id)
            ->where('estado', 'abierta')
            ->latest('fecha_apertura')
            ->first();
    }

    /**
     * Abrir una nueva sesión de caja con bloqueo pesimista contra aperturas concurrentes
     * 
     * @param Caja $caja
     * @param User $user
     * @param float $montoInicial
     * @param string|null $observaciones
     * @return SesionCaja
     * @throws Exception
     */
    public function abrirCaja(Caja $caja, User $user, float $montoInicial, ?string $observaciones = null): SesionCaja
    {
        return DB::transaction(function () use ($caja, $user, $montoInicial, $observaciones) {
            // Bloquear la caja física para prevenir carreras de concurrencia
            $cajaBloqueada = Caja::where('id', $caja->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$cajaBloqueada->activo) {
                throw new Exception("La caja '{$cajaBloqueada->nombre}' se encuentra inactiva.");
            }

            // Verificar si la caja ya tiene una sesión abierta
            $sesionExistente = SesionCaja::where('caja_id', $cajaBloqueada->id)
                ->where('estado', 'abierta')
                ->lockForUpdate()
                ->first();

            if ($sesionExistente) {
                throw new Exception("La caja '{$cajaBloqueada->nombre}' ya cuenta con una sesión abierta (Turno #{$sesionExistente->id}).");
            }

            // Verificar si el usuario ya tiene otra sesión abierta
            $sesionUsuario = SesionCaja::where('user_id', $user->id)
                ->where('estado', 'abierta')
                ->lockForUpdate()
                ->first();

            if ($sesionUsuario) {
                $nombreCaja = $sesionUsuario->caja ? $sesionUsuario->caja->nombre : "Caja #{$sesionUsuario->caja_id}";
                throw new Exception("Ya tienes la sesión #{$sesionUsuario->id} abierta en '{$nombreCaja}'. Debes cerrarla antes de abrir otro turno.");
            }

            $montoInicialValido = max(0, round($montoInicial, 2));

            return SesionCaja::create([
                'caja_id'                 => $cajaBloqueada->id,
                'user_id'                 => $user->id,
                'monto_inicial'           => $montoInicialValido,
                'fecha_apertura'          => now(),
                'observaciones_apertura'  => $observaciones ? trim($observaciones) : null,
                'monto_esperado_efectivo' => $montoInicialValido,
                'estado'                  => 'abierta',
            ]);
        });
    }

    /**
     * Calcular y actualizar los totales en tiempo real de una sesión de caja
     * 
     * @param SesionCaja $sesion
     * @return SesionCaja
     */
    public function recalcularTotales(SesionCaja $sesion): SesionCaja
    {
        // Ventas completadas asociadas a la sesión
        $ventasEfectivo = (float) Venta::where('sesion_caja_id', $sesion->id)
            ->where('estado', 'completada')
            ->where('metodo_pago', 'efectivo')
            ->sum('total');

        $ventasTarjeta = (float) Venta::where('sesion_caja_id', $sesion->id)
            ->where('estado', 'completada')
            ->where('metodo_pago', 'tarjeta')
            ->sum('total');

        $ventasTransferencia = (float) Venta::where('sesion_caja_id', $sesion->id)
            ->where('estado', 'completada')
            ->where('metodo_pago', 'transferencia')
            ->sum('total');

        $ventasOtros = (float) Venta::where('sesion_caja_id', $sesion->id)
            ->where('estado', 'completada')
            ->whereNotIn('metodo_pago', ['efectivo', 'tarjeta', 'transferencia'])
            ->sum('total');

        $totalVentas = round($ventasEfectivo + $ventasTarjeta + $ventasTransferencia + $ventasOtros, 2);

        // Movimientos manuales
        $ingresos = (float) MovimientoCaja::where('sesion_caja_id', $sesion->id)
            ->where('tipo', 'ingreso')
            ->sum('monto');

        $egresos = (float) MovimientoCaja::where('sesion_caja_id', $sesion->id)
            ->where('tipo', 'egreso')
            ->sum('monto');

        $esperadoEfectivo = round((float) $sesion->monto_inicial + $ventasEfectivo + $ingresos - $egresos, 2);

        $sesion->update([
            'total_ventas_efectivo'       => round($ventasEfectivo, 2),
            'total_ventas_tarjeta'        => round($ventasTarjeta, 2),
            'total_ventas_transferencia'  => round($ventasTransferencia, 2),
            'total_ventas_otros'          => round($ventasOtros, 2),
            'total_ventas'                => $totalVentas,
            'total_ingresos_manuales'     => round($ingresos, 2),
            'total_egresos_manuales'      => round($egresos, 2),
            'monto_esperado_efectivo'     => $esperadoEfectivo,
        ]);

        return $sesion;
    }

    /**
     * Registrar un movimiento manual de caja (ingreso o egreso de dinero)
     * 
     * @param SesionCaja $sesion
     * @param User $user
     * @param string $tipo
     * @param float $monto
     * @param string $concepto
     * @param string|null $comprobante
     * @return MovimientoCaja
     * @throws Exception
     */
    public function registrarMovimiento(
        SesionCaja $sesion,
        User $user,
        string $tipo,
        float $monto,
        string $concepto,
        ?string $comprobante = null
    ): MovimientoCaja {
        return DB::transaction(function () use ($sesion, $user, $tipo, $monto, $concepto, $comprobante) {
            $sesionBloqueada = SesionCaja::where('id', $sesion->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$sesionBloqueada->estaAbierta()) {
                throw new Exception("No se pueden registrar movimientos en una sesión de caja cerrada.");
            }

            if ($monto <= 0) {
                throw new Exception("El monto del movimiento debe ser superior a 0.");
            }

            $montoValido = round($monto, 2);

            $movimiento = MovimientoCaja::create([
                'sesion_caja_id'         => $sesionBloqueada->id,
                'user_id'                => $user->id,
                'tipo'                   => $tipo,
                'monto'                  => $montoValido,
                'concepto'               => trim($concepto),
                'comprobante_referencia' => $comprobante ? trim($comprobante) : null,
            ]);

            $this->recalcularTotales($sesionBloqueada);

            return $movimiento;
        });
    }

    /**
     * Realizar el arqueo y cierre formal de caja con cálculo atómico de diferencias
     * 
     * @param SesionCaja $sesion
     * @param User $cerradoPor
     * @param float $montoFinalEfectivo
     * @param string|null $observaciones
     * @return SesionCaja
     * @throws Exception
     */
    public function cerrarCaja(
        SesionCaja $sesion,
        User $cerradoPor,
        float $montoFinalEfectivo,
        ?string $observaciones = null
    ): SesionCaja {
        return DB::transaction(function () use ($sesion, $cerradoPor, $montoFinalEfectivo, $observaciones) {
            $sesionBloqueada = SesionCaja::where('id', $sesion->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$sesionBloqueada->estaAbierta()) {
                throw new Exception("Esta sesión de caja ya se encuentra cerrada.");
            }

            // Recalcular primero todos los totales con datos frescos
            $this->recalcularTotales($sesionBloqueada);
            $sesionBloqueada->refresh();

            $declarado = max(0, round($montoFinalEfectivo, 2));
            $esperado = (float) $sesionBloqueada->monto_esperado_efectivo;
            $diferencia = round($declarado - $esperado, 2);

            $sesionBloqueada->update([
                'fecha_cierre'         => now(),
                'cerrado_por'          => $cerradoPor->id,
                'monto_final_efectivo' => $declarado,
                'diferencia_efectivo'  => $diferencia,
                'estado'               => 'cerrada',
                'observaciones_cierre' => $observaciones ? trim($observaciones) : null,
            ]);

            return $sesionBloqueada;
        });
    }
}
