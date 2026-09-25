<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRecetaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('registrar recetas');
    }

    public function rules(): array
    {
        return [
            'cliente_id' => ['nullable', 'integer', 'exists:clientes,id'],
            'paciente_nombre' => ['required', 'string', 'max:150'],
            'paciente_documento' => ['nullable', 'string', 'max:50'],
            'paciente_edad' => ['nullable', 'integer', 'min:0', 'max:130'],
            'medico_nombre' => ['required', 'string', 'max:150'],
            'medico_colegiatura' => ['required', 'string', 'max:50'],
            'medico_especialidad' => ['nullable', 'string', 'max:100'],
            'institucion_salud' => ['nullable', 'string', 'max:150'],
            'numero_receta' => ['required', 'string', 'max:50', 'unique:recetas,numero_receta'],
            'fecha_emision' => ['required', 'date'],
            'fecha_vencimiento' => ['nullable', 'date', 'after_or_equal:fecha_emision'],
            'tipo_receta' => ['required', 'in:simple,retenida'],
            'archivo_receta' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'observaciones' => ['nullable', 'string'],
            'detalles' => ['required', 'array', 'min:1'],
            'detalles.*.producto_id' => ['required', 'integer', 'exists:productos,id'],
            'detalles.*.cantidad_recetada' => ['required', 'integer', 'min:1'],
            'detalles.*.posologia' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'paciente_nombre.required'     => 'El nombre del paciente es obligatorio.',
            'medico_nombre.required'       => 'El nombre del médico prescriptor es obligatorio.',
            'medico_colegiatura.required'  => 'La cédula / CMP del médico es obligatoria.',
            'medico_colegiatura.max'       => 'La cédula / CMP del médico no puede superar los 50 caracteres.',
            'numero_receta.required'       => 'El número o folio de la receta es obligatorio.',
            'numero_receta.unique'         => 'Ya existe una receta médica registrada con este número de folio.',
            'fecha_emision.required'       => 'La fecha de emisión de la receta es obligatoria.',
            'detalles.required'            => 'Debes incluir al menos un medicamento prescrito.',
            'detalles.min'                 => 'Debes incluir al menos un medicamento prescrito.',
            'detalles.*.producto_id.required' => 'El medicamento es obligatorio.',
            'detalles.*.cantidad_recetada.required' => 'La cantidad prescrita es obligatoria.',
            'detalles.*.cantidad_recetada.min' => 'La cantidad prescrita debe ser mayor a 0.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $detalles = $this->input('detalles');
        if (is_array($detalles)) {
            foreach ($detalles as &$d) {
                if (is_array($d)) {
                    $d['producto_id'] = (!empty($d['producto_id']) && is_numeric($d['producto_id'])) ? (int) $d['producto_id'] : null;
                    $d['cantidad_recetada'] = (!empty($d['cantidad_recetada']) && is_numeric($d['cantidad_recetada'])) ? (int) $d['cantidad_recetada'] : 1;
                    $d['posologia'] = isset($d['posologia']) ? trim((string) $d['posologia']) : null;
                }
            }
            unset($d);
        }

        $this->merge([
            'cliente_id'           => ($this->filled('cliente_id') && is_numeric($this->input('cliente_id'))) ? (int) $this->input('cliente_id') : null,
            'paciente_edad'        => ($this->filled('paciente_edad') && is_numeric($this->input('paciente_edad'))) ? (int) $this->input('paciente_edad') : null,
            'paciente_documento'   => $this->filled('paciente_documento') ? trim((string) $this->input('paciente_documento')) : null,
            'medico_colegiatura'   => $this->filled('medico_colegiatura') ? trim((string) $this->input('medico_colegiatura')) : null,
            'medico_especialidad'  => $this->filled('medico_especialidad') ? trim((string) $this->input('medico_especialidad')) : null,
            'institucion_salud'    => $this->filled('institucion_salud') ? trim((string) $this->input('institucion_salud')) : null,
            'fecha_vencimiento'    => $this->filled('fecha_vencimiento') ? $this->input('fecha_vencimiento') : null,
            'observaciones'        => $this->filled('observaciones') ? trim((string) $this->input('observaciones')) : null,
            'detalles'             => $detalles,
        ]);
    }
}
