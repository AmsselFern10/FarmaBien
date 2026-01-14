<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProveedorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('crear proveedores');
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:150',
            'ruc' => 'required|string|size:11|unique:proveedores,ruc',
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
            'ruc.unique' => 'El RUC ya está registrado.',
            'email.email' => 'El email no tiene un formato válido.',
        ];
    }
}