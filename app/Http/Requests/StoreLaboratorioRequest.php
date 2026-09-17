<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLaboratorioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('crear laboratorios');
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:150', 'unique:laboratorios,nombre'],
            'codigo' => ['nullable', 'string', 'max:50', 'unique:laboratorios,codigo'],
            'contacto' => ['nullable', 'string', 'max:100'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:100'],
            'pais_origen' => ['nullable', 'string', 'max:80'],
            'activo' => ['boolean'],
        ];
    }
}
