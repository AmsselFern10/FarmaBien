<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRecetaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('registrar recetas');
    }

    public function rules(): array
    {
        return [
            'cliente_id' => 'required|exists:clientes,id',
            'medico' => 'required|string|max:100',
            'numero_receta' => 'required|string|max:50|unique:recetas,numero_receta',
            'fecha' => 'required|date|before_or_equal:today',
        ];
    }

    public function messages(): array
    {
        return [
            'cliente_id.required' => 'Debe seleccionar un cliente.',
            'cliente_id.exists' => 'El cliente seleccionado no existe.',
            'medico.required' => 'El nombre del médico es obligatorio.',
            'medico.max' => 'El nombre del médico no puede exceder 100 caracteres.',
            'numero_receta.required' => 'El número de receta es obligatorio.',
            'numero_receta.unique' => 'El número de receta ya está registrado.',
            'fecha.required' => 'La fecha es obligatoria.',
            'fecha.before_or_equal' => 'La fecha no puede ser futura.',
        ];
    }
}