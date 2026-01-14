<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRecetaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('registrar recetas');
    }

    public function rules(): array
    {
        $recetaId = $this->route('receta');

        return [
            'cliente_id' => 'required|exists:clientes,id',
            'medico' => 'required|string|max:100',
            'numero_receta' => [
                'required',
                'string',
                'max:50',
                Rule::unique('recetas')->ignore($recetaId)
            ],
            'fecha' => 'required|date|before_or_equal:today',
        ];
    }

    public function messages(): array
    {
        return [
            'cliente_id.required' => 'Debe seleccionar un cliente.',
            'medico.required' => 'El nombre del médico es obligatorio.',
            'numero_receta.required' => 'El número de receta es obligatorio.',
            'numero_receta.unique' => 'El número de receta ya está registrado en otra receta.',
            'fecha.required' => 'La fecha es obligatoria.',
            'fecha.before_or_equal' => 'La fecha no puede ser futura.',
        ];
    }
}
