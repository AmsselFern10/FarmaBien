<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('editar clientes');
    }

    public function rules(): array
    {
        $clienteId = $this->route('cliente'); // ID del cliente en la ruta

        return [
            'nombre' => 'required|string|max:150',
            'documento' => [
                'nullable',
                'string',
                'max:25',
                Rule::unique('clientes')->ignore($clienteId)
            ],
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
}