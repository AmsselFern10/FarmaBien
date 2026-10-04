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
            'ruc' => 'required|string|max:25|unique:proveedores,ruc',
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
            'nombre.max' => 'El nombre no puede exceder 150 caracteres.',
            'ruc.required' => 'El RUC es obligatorio.',
            'ruc.max' => 'El RUC no puede exceder 25 caracteres.',
            'ruc.unique' => 'El RUC ya está registrado en otro proveedor.',
            'email.email' => 'El email no tiene un formato válido.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $contacto  = trim((string)$this->input('contacto', ''));
        $telefono  = trim((string)$this->input('telefono', ''));
        $email     = trim((string)$this->input('email', ''));
        $direccion = trim((string)$this->input('direccion', ''));

        $this->merge([
            'nombre'    => trim((string)$this->input('nombre', '')),
            'ruc'       => strtoupper(trim((string)$this->input('ruc', ''))),
            'contacto'  => $contacto !== '' ? $contacto : null,
            'telefono'  => $telefono !== '' ? $telefono : null,
            'email'     => $email !== '' ? $email : null,
            'direccion' => $direccion !== '' ? $direccion : null,
            'activo'    => $this->has('activo') ? $this->boolean('activo') : true,
        ]);
    }
}