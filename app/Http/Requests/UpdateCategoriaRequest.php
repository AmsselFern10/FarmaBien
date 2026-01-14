<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('editar categorias');
    }

    public function rules(): array
    {
        $categoriaId = $this->route('categoria'); // ID de la categoría en la ruta

        return [
            'nombre' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categorias')->ignore($categoriaId)
            ],
            'descripcion' => 'nullable|string|max:200',
            'activo' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la categoría es obligatorio.',
            'nombre.unique' => 'Ya existe otra categoría con ese nombre.',
            'nombre.max' => 'El nombre no puede exceder 100 caracteres.',
        ];
    }
}