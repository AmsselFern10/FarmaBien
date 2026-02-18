<?php

namespace App\Http\Requests\Reportes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class ProductosMasVendidosReporteRequest extends FormRequest
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
            'limite' => $this->input('limite', 20),
        ]);
    }

    public function rules(): array
    {
        return [
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'limite' => ['required', 'integer', 'min:1', 'max:200'],

            // Export
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
            'limite' => (int) $this->input('limite', 20),
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
