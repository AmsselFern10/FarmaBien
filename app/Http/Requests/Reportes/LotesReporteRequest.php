<?php

namespace App\Http\Requests\Reportes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class LotesReporteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'proximos_dias' => $this->input('proximos_dias') === null ? null : (int) $this->input('proximos_dias'),
        ]);
    }

    public function rules(): array
    {
        return [
            'producto_id' => ['nullable', 'integer'],
            'proveedor_id' => ['nullable', 'integer'],
            'estado' => ['nullable', 'in:activo,agotado,vencido,bloqueado'],

            'activo' => ['nullable', 'boolean'],
            'con_stock' => ['nullable', 'boolean'],
            'vencidos' => ['nullable', 'boolean'],
            'proximos_dias' => ['nullable', 'integer', 'min:1', 'max:365'],

            'ingreso_inicio' => ['nullable', 'date'],
            'ingreso_fin' => ['nullable', 'date', 'after_or_equal:ingreso_inicio'],

            'export' => ['nullable', 'in:csv,excel,pdf'],
        ];
    }

    public function filters(): array
    {
        $ingresoInicio = $this->input('ingreso_inicio') ? Carbon::parse($this->input('ingreso_inicio'))->startOfDay() : null;
        $ingresoFin = $this->input('ingreso_fin') ? Carbon::parse($this->input('ingreso_fin'))->endOfDay() : null;

        return [
            'producto_id' => $this->input('producto_id'),
            'proveedor_id' => $this->input('proveedor_id'),
            'estado' => $this->input('estado'),
            'activo' => $this->has('activo') ? (bool) $this->boolean('activo') : null,
            'con_stock' => $this->has('con_stock') ? (bool) $this->boolean('con_stock') : null,
            'vencidos' => $this->has('vencidos') ? (bool) $this->boolean('vencidos') : null,
            'proximos_dias' => $this->input('proximos_dias'),
            'ingreso_inicio' => $ingresoInicio,
            'ingreso_fin' => $ingresoFin,
        ];
    }
}
