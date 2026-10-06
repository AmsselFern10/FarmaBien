<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCotizacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('registrar compras');
    }

    public function rules(): array
    {
        return [
            'producto_id'     => ['required', 'integer', 'exists:productos,id'],
            'proveedor_id'    => ['required', 'integer', 'exists:proveedores,id'],
            'presentacion_id' => ['nullable', 'integer', 'exists:presentaciones_producto,id'],
            'precio_compra'   => ['required', 'numeric', 'min:0.0001'],
            'fecha'           => ['required', 'date', 'before_or_equal:today'],
            'observaciones'   => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'producto_id.required'     => 'Debe seleccionar un producto o medicamento válido.',
            'producto_id.exists'       => 'El producto seleccionado no existe en el catálogo.',
            'proveedor_id.required'    => 'Debe seleccionar un proveedor distribuidor.',
            'proveedor_id.exists'      => 'El proveedor seleccionado no existe.',
            'presentacion_id.exists'   => 'La presentación seleccionada no es válida.',
            'precio_compra.required'   => 'El precio cotizado es obligatorio.',
            'precio_compra.min'        => 'El precio cotizado debe ser mayor a C$ 0.0000.',
            'fecha.required'           => 'La fecha de la cotización es obligatoria.',
            'fecha.before_or_equal'    => 'La fecha de la cotización no puede ser futura.',
        ];
    }

    public function attributes(): array
    {
        return [
            'producto_id'     => 'medicamento',
            'proveedor_id'    => 'proveedor',
            'presentacion_id' => 'presentación comercial',
            'precio_compra'   => 'precio cotizado',
            'fecha'           => 'fecha de cotización',
            'observaciones'   => 'observaciones o condiciones',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'producto_id'     => $this->filled('producto_id') ? (int) $this->input('producto_id') : null,
            'proveedor_id'    => $this->filled('proveedor_id') ? (int) $this->input('proveedor_id') : null,
            'presentacion_id' => $this->filled('presentacion_id') ? (int) $this->input('presentacion_id') : null,
            'fecha'           => $this->filled('fecha') ? trim($this->input('fecha')) : now()->toDateString(),
            'observaciones'   => $this->filled('observaciones') ? trim($this->input('observaciones')) : null,
        ]);
    }
}
