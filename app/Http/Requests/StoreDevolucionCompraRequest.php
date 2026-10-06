<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDevolucionCompraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('registrar compras');
    }

    public function rules(): array
    {
        return [
            'proveedor_id'            => ['required', 'integer', 'exists:proveedores,id'],
            'compra_id'               => ['nullable', 'integer', 'exists:compras,id'],
            'motivo'                  => ['required', 'string', 'min:5', 'max:1000'],
            'items'                   => ['required', 'array', 'min:1'],
            'items.*.lote_id'         => ['required', 'integer', 'exists:lotes,id'],
            'items.*.cantidad'        => ['required', 'integer', 'min:1'],
            'items.*.precio_unitario' => ['nullable', 'numeric', 'min:0'],
            'items.*.motivo_detalle'  => ['nullable', 'string', 'max:300'],
        ];
    }

    public function messages(): array
    {
        return [
            'proveedor_id.required'     => 'Debe seleccionar el proveedor destinatario.',
            'proveedor_id.exists'       => 'El proveedor seleccionado no existe.',
            'compra_id.exists'          => 'La factura de compra seleccionada no es válida.',
            'motivo.required'           => 'El motivo de la devolución es obligatorio.',
            'motivo.min'                => 'El motivo debe tener al menos 5 caracteres.',
            'items.required'            => 'Debe agregar al menos un lote para devolver.',
            'items.min'                 => 'Debe agregar al menos un lote para devolver.',
            'items.*.lote_id.required'  => 'Debe seleccionar un lote válido en cada línea.',
            'items.*.lote_id.exists'    => 'Uno de los lotes seleccionados no existe.',
            'items.*.cantidad.required' => 'La cantidad a devolver es obligatoria.',
            'items.*.cantidad.min'      => 'La cantidad a devolver debe ser de al menos 1 unidad.',
            'items.*.precio_unitario.min' => 'El precio o costo unitario no puede ser negativo.',
        ];
    }

    public function attributes(): array
    {
        return [
            'proveedor_id'            => 'proveedor',
            'compra_id'               => 'compra de origen',
            'motivo'                  => 'motivo general',
            'items'                   => 'lotes a devolver',
            'items.*.lote_id'         => 'lote',
            'items.*.cantidad'        => 'cantidad',
            'items.*.precio_unitario' => 'costo unitario',
            'items.*.motivo_detalle'  => 'motivo por ítem',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'motivo'    => $this->filled('motivo') ? trim($this->input('motivo')) : null,
            'compra_id' => $this->filled('compra_id') ? (int) $this->input('compra_id') : null,
        ]);
    }
}
