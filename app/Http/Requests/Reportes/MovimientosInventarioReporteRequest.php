<?php

namespace App\Http\Requests\Reportes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class MovimientosInventarioReporteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $fi = $this->input('fecha_inicio');
        $ff = $this->input('fecha_fin');

        $this->merge([
            'fecha_inicio' => $fi ?: now()->startOfMonth()->toDateString(),
            'fecha_fin' => $ff ?: now()->toDateString(),
        ]);
    }

    public function rules(): array
    {
        return [
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'tipo' => ['nullable', 'in:entrada,salida,ajuste'],

            'producto_id' => ['nullable', 'integer'],
            'lote_id' => ['nullable', 'integer'],
            'user_id' => ['nullable', 'integer'],
            'origen' => ['nullable', 'string', 'max:50'],
            'motivo' => ['nullable', 'string', 'max:255'],

            'export' => ['nullable', 'in:csv,excel,pdf'],
        ];
    }

    public function filters(): array
    {
        $inicio = Carbon::parse($this->input('fecha_inicio'))->startOfDay();
        $fin = Carbon::parse($this->input('fecha_fin'))->endOfDay();

        return [
            'inicio' => $inicio,
            'fin' => $fin,
            'tipo' => $this->input('tipo'),
            'producto_id' => $this->input('producto_id'),
            'lote_id' => $this->input('lote_id'),
            'user_id' => $this->input('user_id'),
            'origen' => $this->input('origen'),
            'motivo' => $this->input('motivo'),
        ];
    }

    public function fechaInicioString(): string
    {
        return Carbon::parse($this->input('fecha_inicio'))->toDateString();
    }

    public function fechaFinString(): string
    {
        return Carbon::parse($this->input('fecha_fin'))->toDateString();
    }
}
