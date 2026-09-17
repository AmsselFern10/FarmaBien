<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProveedorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('editar proveedores');
    }

    public function rules(): array
    {
        $proveedorId = $this->route('proveedor'); // ID del proveedor en la ruta

        return [
            'nombre' => 'required|string|max:150',
            'ruc' => [
                'required',
                'string',
                'size:11',
                Rule::unique('proveedores')->ignore($proveedorId)
            ],
            'contacto' => 'nullable|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'direccion' => 'nullable|string|max:500',
            'activo' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del proveedor es obligatorio.',
            'ruc.required' => 'El RUC es obligatorio.',
            'ruc.size' => 'El RUC debe tener exactamente 11 dígitos.',
            'ruc.unique' => 'El RUC ya está registrado en otro proveedor.',
            'email.email' => 'El email no tiene un formato válido.',
        ];
    }
}