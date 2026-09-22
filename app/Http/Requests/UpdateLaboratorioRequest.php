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

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del laboratorio es obligatorio.',
            'nombre.unique' => 'Ya existe un laboratorio registrado con ese nombre.',
            'codigo.unique' => 'El código de laboratorio ya está en uso.',
            'email.email' => 'El formato del correo electrónico no es válido.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $codigo = trim((string)$this->input('codigo', ''));
        $contacto = trim((string)$this->input('contacto', ''));
        $telefono = trim((string)$this->input('telefono', ''));
        $email = trim((string)$this->input('email', ''));
        $pais = trim((string)$this->input('pais_origen', ''));

        $this->merge([
            'nombre' => trim((string)$this->input('nombre', '')),
            'codigo' => $codigo !== '' ? $codigo : null,
            'contacto' => $contacto !== '' ? $contacto : null,
            'telefono' => $telefono !== '' ? $telefono : null,
            'email' => $email !== '' ? $email : null,
            'pais_origen' => $pais !== '' ? $pais : null,
            'activo' => $this->has('activo') ? $this->boolean('activo') : true,
        ]);
    }
}
