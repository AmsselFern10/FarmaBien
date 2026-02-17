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
        $recetaId = $this->route('receta')?->id;

        return [
            'cliente_id' => ['required', 'integer', 'exists:clientes,id'],
            'medico' => ['required', 'string', 'max:100'],
            'especialidad' => ['nullable', 'string', 'max:100'],

            'numero_receta' => ['required', 'string', 'max:50', Rule::unique('recetas', 'numero_receta')->ignore($recetaId)],
            'fecha' => ['required', 'date'],

            'diagnostico' => ['nullable', 'string', 'max:2000'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'cliente_id.required' => 'Debe seleccionar un cliente.',
            'cliente_id.exists' => 'El cliente seleccionado no existe.',
            'numero_receta.unique' => 'El número de receta ya está registrado.',
        ];
    }
}
