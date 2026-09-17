<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLaboratorioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('editar laboratorios');
    }

    public function rules(): array
    {
        $laboratorioId = $this->route('laboratorio')?->id ?? $this->route('laboratorio');

        return [
            'nombre' => ['required', 'string', 'max:150', Rule::unique('laboratorios', 'nombre')->ignore($laboratorioId)],
            'codigo' => ['nullable', 'string', 'max:50', Rule::unique('laboratorios', 'codigo')->ignore($laboratorioId)],
            'contacto' => ['nullable', 'string', 'max:100'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:100'],
            'pais_origen' => ['nullable', 'string', 'max:80'],
            'activo' => ['boolean'],
        ];
    }
}
