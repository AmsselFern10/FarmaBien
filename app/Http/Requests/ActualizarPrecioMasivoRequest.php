<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ActualizarPrecioMasivoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('editar precios') || $this->user()->can('editar productos');
    }

    public function rules(): array
    {
        return [
            'tipo_alcance' => ['required', 'string', 'in:todo,categoria,laboratorio,proveedor,productos'],
            'alcance_ids'  => ['nullable'],
            'tipo_ajuste'  => ['required', 'string', 'in:porcentaje_aumento,porcentaje_disminucion,monto_aumento,monto_disminucion,fijo'],
            'valor_ajuste' => ['required', 'numeric', 'min:0'],
            'redondeo'     => ['nullable', 'string', 'in:sin,0.05,unidad'],
            'motivo'       => ['required', 'string', 'min:3', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'tipo_alcance.required' => 'Debe especificar el alcance del ajuste.',
            'tipo_ajuste.required'  => 'Debe seleccionar el tipo de ajuste de precio.',
            'valor_ajuste.required' => 'Debe indicar el valor del ajuste.',
            'motivo.required'       => 'El motivo del cambio masivo es obligatorio.',
        ];
    }
}
