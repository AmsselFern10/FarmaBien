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
        $categoriaId = $this->route('categoria')?->id ?? $this->route('categoria');

        return [
            'nombre' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categorias', 'nombre')->ignore($categoriaId)
            ],
            'descripcion' => ['nullable', 'string', 'max:200'],
            'activo' => ['boolean'],
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

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nombre' => trim((string)$this->input('nombre', '')),
            'descripcion' => $this->filled('descripcion') ? trim((string)$this->input('descripcion')) : null,
            'activo' => $this->has('activo') ? $this->boolean('activo') : true,
        ]);
    }
}