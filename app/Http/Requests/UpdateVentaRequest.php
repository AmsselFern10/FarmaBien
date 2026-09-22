<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('realizar ventas');
    }

    public function rules(): array
    {
        return [
            'cliente_id'                  => ['nullable', 'integer', 'exists:clientes,id'],
            'tipo_comprobante'            => ['required', 'string', 'in:ticket,boleta,factura'],
            'serie'                       => ['nullable', 'string', 'max:20'],
            'numero_comprobante'          => ['nullable', 'string', 'max:50'],
            'metodo_pago'                 => ['required', 'string', 'in:efectivo,tarjeta,transferencia,mixto'],
            'descuento'                   => ['nullable', 'numeric', 'min:0'],
            'motivo_modificacion'         => ['required', 'string', 'min:5', 'max:255'],
            'productos'                   => ['required', 'array', 'min:1'],
            'productos.*.producto_id'     => ['required', 'integer', 'exists:productos,id'],
            'productos.*.lote_id'         => ['required', 'integer', 'exists:lotes,id'],
            'productos.*.presentacion_id' => ['nullable', 'integer', 'exists:presentaciones_producto,id'],
            'productos.*.receta_detalle_id' => ['nullable', 'integer', 'exists:receta_detalles,id'],
            'productos.*.cantidad'        => ['required', 'integer', 'min:1'],
            'productos.*.precio_unitario' => ['nullable', 'numeric', 'min:0'],
            'recetas'                     => ['nullable', 'array'],
            'recetas.*'                   => ['integer', 'exists:recetas,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'motivo_modificacion.required' => 'Debes ingresar el motivo de la modificación de la venta.',
            'motivo_modificacion.min'      => 'El motivo debe tener al menos 5 caracteres.',
            'productos.required'           => 'Debes incluir al menos un producto en la venta.',
            'productos.min'                => 'Debes incluir al menos un producto en la venta.',
            'productos.*.producto_id.required' => 'El medicamento es obligatorio en cada línea.',
            'productos.*.producto_id.exists'   => 'Uno de los medicamentos seleccionados no existe.',
            'productos.*.lote_id.required'     => 'Debes seleccionar un lote válido para cada producto.',
            'productos.*.lote_id.exists'       => 'El lote seleccionado no existe.',
            'productos.*.cantidad.required'    => 'La cantidad es obligatoria.',
            'productos.*.cantidad.min'         => 'La cantidad debe ser al menos 1 unidad.',
            'metodo_pago.required'         => 'Debes seleccionar un método de pago.',
            'metodo_pago.in'               => 'El método de pago no es válido.',
            'tipo_comprobante.required'    => 'Debes seleccionar el tipo de comprobante.',
            'tipo_comprobante.in'          => 'El tipo de comprobante seleccionado no es válido.',
            'descuento.min'                => 'El descuento no puede ser un valor negativo.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'motivo_modificacion' => $this->filled('motivo_modificacion') ? trim($this->input('motivo_modificacion')) : null,
            'tipo_comprobante'    => $this->filled('tipo_comprobante') ? trim($this->input('tipo_comprobante')) : 'ticket',
            'metodo_pago'         => $this->filled('metodo_pago') ? trim($this->input('metodo_pago')) : 'efectivo',
            'serie'               => $this->filled('serie') ? trim($this->input('serie')) : null,
            'numero_comprobante'  => $this->filled('numero_comprobante') ? trim($this->input('numero_comprobante')) : null,
            'descuento'           => $this->filled('descuento') ? max(0, (float) $this->input('descuento')) : 0,
            'cliente_id'          => $this->filled('cliente_id') ? (int) $this->input('cliente_id') : null,
        ]);
    }
}
