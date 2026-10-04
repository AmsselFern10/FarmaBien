<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('crear clientes');
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:150',
            'documento' => 'nullable|string|max:25|unique:clientes,documento',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'direccion' => 'nullable|string|max:500',
            'activo' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del cliente es obligatorio.',
            'nombre.max' => 'El nombre no puede exceder 150 caracteres.',
            'documento.unique' => 'El documento ya está registrado en otro cliente.',
            'email.email' => 'El email no tiene un formato válido.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $documento = trim((string)$this->input('documento', ''));
        $telefono  = trim((string)$this->input('telefono', ''));
        $email     = trim((string)$this->input('email', ''));
        $direccion = trim((string)$this->input('direccion', ''));

        $this->merge([
            'nombre'    => trim((string)$this->input('nombre', '')),
            'documento' => $documento !== '' ? $documento : null,
            'telefono'  => $telefono !== '' ? $telefono : null,
            'email'     => $email !== '' ? $email : null,
            'direccion' => $direccion !== '' ? $direccion : null,
            'activo'    => $this->has('activo') ? $this->boolean('activo') : true,
        ]);
    }
}
