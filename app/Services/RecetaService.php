<?php

namespace App\Services;

use App\Models\Receta;
use App\Models\RecetaDetalle;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;
use Exception;

class RecetaService
{
    /**
     * Registrar una nueva receta médica con sus medicamentos prescritos
     * 
     * @param array $data
     * @return Receta
     * @throws Exception
     */
    public function registrarReceta(array $data): Receta
    {
        return DB::transaction(function () use ($data) {
            
            if (empty($data['detalles']) || !is_array($data['detalles'])) {
                throw new Exception('La receta médica debe incluir al menos un medicamento prescrito.');
            }

            // Validar unicidad del número de receta
            $existe = Receta::where('numero_receta', $data['numero_receta'])->exists();
            if ($existe) {
                throw new Exception("Ya existe una receta registrada con el número '{$data['numero_receta']}'.");
            }

            // 1. Crear cabecera de la receta
            $receta = Receta::create([
                'cliente_id' => $data['cliente_id'] ?? null,
                'paciente_nombre' => $data['paciente_nombre'],
                'paciente_documento' => $data['paciente_documento'] ?? null,
                'paciente_edad' => $data['paciente_edad'] ?? null,
                'medico_nombre' => $data['medico_nombre'],
                'medico_colegiatura' => $data['medico_colegiatura'],
                'medico_especialidad' => $data['medico_especialidad'] ?? null,
                'institucion_salud' => $data['institucion_salud'] ?? null,
                'numero_receta' => $data['numero_receta'],
                'fecha_emision' => $data['fecha_emision'] ?? now()->toDateString(),
                'fecha_vencimiento' => $data['fecha_vencimiento'] ?? null,
                'tipo_receta' => $data['tipo_receta'] ?? 'simple',
                'archivo_receta' => $data['archivo_receta'] ?? null,
                'estado' => 'pendiente',
                'observaciones' => $data['observaciones'] ?? null,
            ]);

            // 2. Registrar cada medicamento prescrito
            foreach ($data['detalles'] as $item) {
                $producto = Producto::findOrFail($item['producto_id']);
                $cantidad = (int) $item['cantidad_recetada'];

                if ($cantidad <= 0) {
                    throw new Exception("La cantidad recetada para {$producto->nombre} debe ser mayor a 0.");
                }

                RecetaDetalle::create([
                    'receta_id' => $receta->id,
                    'producto_id' => $producto->id,
                    'cantidad_recetada' => $cantidad,
                    'cantidad_dispensada' => 0,
                    'posologia' => $item['posologia'] ?? null,
                ]);
            }

            return $receta->load(['detalles.producto', 'cliente']);
        });
    }

    /**
     * Dispensar una cantidad de medicamento de una receta (con bloqueo de concurrencia)
     * 
     * @param int $recetaDetalleId
     * @param int $cantidad
     * @return RecetaDetalle
     * @throws Exception
     */
    public function dispensarMedicamento(int $recetaDetalleId, int $cantidad): RecetaDetalle
    {
        $detalle = RecetaDetalle::with('receta')
            ->where('id', $recetaDetalleId)
            ->lockForUpdate()
            ->firstOrFail();

        $receta = $detalle->receta;

        if ($receta->estado === 'anulada') {
            throw new Exception("No se puede dispensar: la receta #{$receta->numero_receta} está anulada.");
        }

        if ($receta->estaVencida()) {
            throw new Exception("No se puede dispensar: la receta médica venció el {$receta->fecha_vencimiento->format('d/m/Y')}.");
        }

        $pendiente = $detalle->pendiente_dispensar;
        if ($cantidad > $pendiente) {
            throw new Exception(
                "La cantidad solicitada ({$cantidad}) excede el saldo pendiente ({$pendiente}) de la receta para {$detalle->producto->nombre}."
            );
        }

        $detalle->cantidad_dispensada += $cantidad;
        $detalle->save();

        $this->actualizarEstadoReceta($receta);

        return $detalle;
    }

    /**
     * Revertir dispensación (ej: al anular una venta)
     * 
     * @param int $recetaDetalleId
     * @param int $cantidad
     * @return RecetaDetalle
     */
    public function revertirDispensacion(int $recetaDetalleId, int $cantidad): RecetaDetalle
    {
        $detalle = RecetaDetalle::with('receta')
            ->where('id', $recetaDetalleId)
            ->lockForUpdate()
            ->firstOrFail();

        $detalle->cantidad_dispensada = max(0, $detalle->cantidad_dispensada - $cantidad);
        $detalle->save();

        $this->actualizarEstadoReceta($detalle->receta);

        return $detalle;
    }

    /**
     * Actualizar estado global de la receta (pendiente, dispensada_parcial, dispensada_total)
     * 
     * @param Receta $receta
     */
    protected function actualizarEstadoReceta(Receta $receta): void
    {
        $detalles = $receta->detalles()->get();
        $totalRecetado = $detalles->sum('cantidad_recetada');
        $totalDispensado = $detalles->sum('cantidad_dispensada');

        if ($totalDispensado <= 0) {
            $nuevoEstado = 'pendiente';
        } elseif ($totalDispensado >= $totalRecetado) {
            $nuevoEstado = 'dispensada_total';
        } else {
            $nuevoEstado = 'dispensada_parcial';
        }

        if ($receta->estado !== 'anulada') {
            $receta->update(['estado' => $nuevoEstado]);
        }
    }

    /**
     * Buscar recetas vigentes disponibles para un paciente o cliente
     * 
     * @param string $termino
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function buscarRecetasDisponibles(string $termino)
    {
        return Receta::with(['detalles.producto', 'cliente'])
            ->pendientes()
            ->vigentes()
            ->where(function ($query) use ($termino) {
                $query->where('numero_receta', 'like', "%{$termino}%")
                    ->orWhere('paciente_nombre', 'like', "%{$termino}%")
                    ->orWhere('medico_nombre', 'like', "%{$termino}%");
            })
            ->orderBy('fecha_emision', 'desc')
            ->get();
    }
}
