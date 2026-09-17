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
        $recetaId = $this->route('receta')?->id ?? $this->route('receta');

        return [
            'cliente_id' => ['nullable', 'integer', 'exists:clientes,id'],
            'paciente_nombre' => ['required', 'string', 'max:150'],
            'paciente_documento' => ['nullable', 'string', 'max:50'],
            'paciente_edad' => ['nullable', 'integer', 'min:0', 'max:130'],
            'medico_nombre' => ['required', 'string', 'max:150'],
            'medico_colegiatura' => ['required', 'string', 'max:50'],
            'medico_especialidad' => ['nullable', 'string', 'max:100'],
            'institucion_salud' => ['nullable', 'string', 'max:150'],
            'numero_receta' => ['required', 'string', 'max:50', Rule::unique('recetas', 'numero_receta')->ignore($recetaId)],
            'fecha_emision' => ['required', 'date'],
            'fecha_vencimiento' => ['nullable', 'date', 'after_or_equal:fecha_emision'],
            'tipo_receta' => ['required', 'in:simple,retenida'],
            'archivo_receta' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'observaciones' => ['nullable', 'string'],
        ];
    }
}
