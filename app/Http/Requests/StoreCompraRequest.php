<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('registrar compras');
    }

    public function rules(): array
    {
        return [
            'proveedor_id' => ['required', 'integer', 'exists:proveedores,id'],
            'numero_comprobante' => ['nullable', 'string', 'max:50'],
            'fecha' => ['required', 'date'],
            'productos' => ['required', 'array', 'min:1'],
            'productos.*.producto_id' => ['required', 'integer', 'exists:productos,id'],
            'productos.*.presentacion_id' => ['nullable', 'integer', 'exists:presentaciones_producto,id'],
            'productos.*.cantidad_presentaciones' => ['nullable', 'integer', 'min:1'],
            'productos.*.cantidad' => ['nullable', 'integer', 'min:1'],
            'productos.*.precio_unitario' => ['required', 'numeric', 'min:0'],
            'productos.*.numero_lote' => ['required', 'string', 'max:50'],
            'productos.*.fecha_vencimiento' => ['required', 'date', 'after:today'],
        ];
    }
}
