<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('registrar compras');
    }

    public function rules(): array
    {
        return [
            'proveedor_id'                        => ['required', 'integer', 'exists:proveedores,id'],
            'numero_comprobante'                  => ['nullable', 'string', 'max:50'],
            'fecha'                               => ['required', 'date'],
            'motivo_modificacion'                 => ['required', 'string', 'min:5', 'max:255'],
            'productos'                           => ['required', 'array', 'min:1'],
            'productos.*.producto_id'             => ['required', 'integer', 'exists:productos,id'],
            'productos.*.presentacion_id'         => ['nullable', 'integer', 'exists:presentaciones_producto,id'],
            'productos.*.cantidad_presentaciones' => ['nullable', 'integer', 'min:1'],
            'productos.*.cantidad'                => ['nullable', 'integer', 'min:1'],
            'productos.*.precio_unitario'         => ['required', 'numeric', 'min:0'],
            'productos.*.numero_lote'             => ['required', 'string', 'max:50'],
            'productos.*.fecha_vencimiento'       => ['required', 'date', 'after:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'motivo_modificacion.required'           => 'Debes ingresar el motivo de la modificación.',
            'motivo_modificacion.min'                => 'El motivo debe tener al menos 5 caracteres.',
            'proveedor_id.required'                  => 'Debes seleccionar un proveedor válido.',
            'proveedor_id.exists'                    => 'El proveedor seleccionado no existe.',
            'fecha.required'                         => 'La fecha de la compra es obligatoria.',
            'productos.required'                     => 'Debes registrar al menos un producto en la compra.',
            'productos.min'                          => 'Debes registrar al menos un producto en la compra.',
            'productos.*.producto_id.required'       => 'El producto es obligatorio en cada línea.',
            'productos.*.producto_id.exists'         => 'Uno de los productos seleccionados no existe.',
            'productos.*.precio_unitario.required'   => 'El precio de compra es obligatorio.',
            'productos.*.precio_unitario.min'        => 'El precio de compra no puede ser negativo.',
            'productos.*.numero_lote.required'       => 'El número de lote es obligatorio.',
            'productos.*.fecha_vencimiento.required' => 'La fecha de vencimiento del lote es obligatoria.',
            'productos.*.fecha_vencimiento.after'    => 'La fecha de vencimiento debe ser posterior al día de hoy.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'motivo_modificacion' => $this->filled('motivo_modificacion') ? trim($this->input('motivo_modificacion')) : null,
            'numero_comprobante'  => $this->filled('numero_comprobante') ? trim($this->input('numero_comprobante')) : null,
            'proveedor_id'        => $this->filled('proveedor_id') ? (int) $this->input('proveedor_id') : null,
            'fecha'               => $this->filled('fecha') ? trim($this->input('fecha')) : now()->toDateString(),
        ]);
    }
}
