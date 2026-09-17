<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('realizar ventas');
    }

    public function rules(): array
    {
        return [
            'cliente_id' => ['nullable', 'integer', 'exists:clientes,id'],
            'tipo_comprobante' => ['required', 'in:ticket,boleta,factura'],
            'serie' => ['nullable', 'string', 'max:20'],
            'numero_comprobante' => ['nullable', 'string', 'max:50'],
            'metodo_pago' => ['required', 'in:efectivo,tarjeta,transferencia,mixto'],
            'descuento' => ['nullable', 'numeric', 'min:0'],
            'productos' => ['required', 'array', 'min:1'],
            'productos.*.producto_id' => ['required', 'integer', 'exists:productos,id'],
            'productos.*.lote_id' => ['required', 'integer', 'exists:lotes,id'],
            'productos.*.presentacion_id' => ['nullable', 'integer', 'exists:presentaciones_producto,id'],
            'productos.*.receta_detalle_id' => ['nullable', 'integer', 'exists:receta_detalles,id'],
            'productos.*.cantidad' => ['required', 'integer', 'min:1'],
            'productos.*.precio_unitario' => ['nullable', 'numeric', 'min:0'],
            'recetas' => ['nullable', 'array'],
            'recetas.*' => ['integer', 'exists:recetas,id'],
        ];
    }
}
