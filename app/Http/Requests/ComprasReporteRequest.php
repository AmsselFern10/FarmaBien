<?php

namespace App\Http\Requests\Reportes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class ComprasReporteRequest extends FormRequest
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

            'user_id' => ['nullable', 'integer'],
            'proveedor_id' => ['nullable', 'integer'],

            // Filtros avanzados
            'producto_id' => ['nullable', 'integer'],
            'categoria_id' => ['nullable', 'integer'],

            // Modo / agrupación / orden
            'modo' => ['nullable', 'in:detalle,resumen'],
            'group_by' => ['nullable', 'in:proveedor,usuario,producto,categoria'],
            'order_dir' => ['nullable', 'in:asc,desc'],
        ];
    }

    public function filters(): array
    {
        $inicio = Carbon::parse($this->input('fecha_inicio'))->startOfDay();
        $fin = Carbon::parse($this->input('fecha_fin'))->endOfDay();

        return [
            'inicio' => $inicio,
            'fin' => $fin,
            'user_id' => $this->input('user_id'),
            'proveedor_id' => $this->input('proveedor_id'),
            'producto_id' => $this->input('producto_id'),
            'categoria_id' => $this->input('categoria_id'),
            'modo' => $this->input('modo', 'detalle'),
            'group_by' => $this->input('group_by'),
            'order_dir' => $this->input('order_dir', 'desc'),
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
