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
            'cliente_id'                  => ['nullable', 'integer', 'exists:clientes,id'],
            'tipo_comprobante'            => ['required', 'string', 'in:ticket,boleta,factura'],
            'serie'                       => ['nullable', 'string', 'max:20'],
            'numero_comprobante'          => ['nullable', 'string', 'max:50'],
            'idempotency_key'             => ['nullable', 'string', 'max:64'],
            'metodo_pago'                 => ['required', 'string', 'in:efectivo,tarjeta,transferencia,mixto'],
            'monto_recibido'              => ['required_if:metodo_pago,efectivo', 'nullable', 'numeric', 'min:0'],
            'descuento'                   => ['nullable', 'numeric', 'min:0'],
            'tipo_descuento'              => ['nullable', 'string', 'in:monto,porcentaje'],
            'porcentaje_descuento'        => ['nullable', 'numeric', 'min:0', 'max:100'],
            'receta_modalidad'            => ['nullable', 'string', 'in:sin_receta,vinculada,creada,omitida'],
            'receta_id'                   => ['nullable', 'integer', 'exists:recetas,id'],
            'receta_omision_motivo'       => ['nullable', 'string', 'max:500'],
            'receta_crear'                => ['nullable', 'array'],
            'receta_crear.medico_nombre'  => ['nullable', 'string', 'max:150'],
            'receta_crear.medico_colegiatura' => ['nullable', 'string', 'max:50'],
            'receta_crear.medico_especialidad' => ['nullable', 'string', 'max:100'],
            'receta_crear.paciente_nombre' => ['nullable', 'string', 'max:150'],
            'receta_crear.paciente_documento' => ['nullable', 'string', 'max:50'],
            'receta_crear.numero_receta'  => ['nullable', 'string', 'max:50'],
            'referencia_pago'             => ['nullable', 'string', 'max:100'],
            'observaciones'               => ['nullable', 'string', 'max:500'],
            'productos'                   => ['required', 'array', 'min:1'],
            'productos.*.producto_id'     => ['required', 'integer', 'exists:productos,id'],
            'productos.*.lote_id'         => ['required', 'integer', 'exists:lotes,id'],
            'productos.*.presentacion_id' => ['nullable', 'integer', 'exists:presentaciones_producto,id'],
            'productos.*.receta_detalle_id' => ['nullable', 'integer', 'exists:receta_detalles,id'],
            'productos.*.cantidad'        => ['required', 'integer', 'min:1'],
            'productos.*.precio_unitario' => ['nullable', 'numeric', 'min:0'],
            'productos.*.descuento'       => ['nullable', 'numeric', 'min:0'],
            'productos.*.tipo_descuento'  => ['nullable', 'string', 'in:monto,porcentaje'],
            'productos.*.porcentaje_descuento' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'recetas'                     => ['nullable', 'array'],
            'recetas.*'                   => ['integer', 'exists:recetas,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'productos.required'          => 'Debes incluir al menos un producto en el carrito de venta.',
            'productos.min'               => 'Debes incluir al menos un producto en el carrito de venta.',
            'productos.*.producto_id.required' => 'El medicamento es obligatorio en cada línea.',
            'productos.*.producto_id.exists'   => 'Uno de los medicamentos seleccionados no existe.',
            'productos.*.lote_id.required'     => 'Debes seleccionar un lote válido para cada producto.',
            'productos.*.lote_id.exists'       => 'El lote seleccionado no existe.',
            'productos.*.cantidad.required'    => 'La cantidad es obligatoria.',
            'productos.*.cantidad.min'         => 'La cantidad debe ser al menos 1 unidad.',
            'metodo_pago.required'        => 'Debes seleccionar un método de pago.',
            'metodo_pago.in'              => 'El método de pago no es válido.',
            'monto_recibido.required_if'  => 'En ventas con método Efectivo, el Monto Entregado / Recibido es obligatorio.',
            'monto_recibido.numeric'      => 'El monto recibido debe ser un valor numérico válido.',
            'monto_recibido.min'          => 'El monto recibido no puede ser negativo.',
            'tipo_comprobante.required'   => 'Debes seleccionar el tipo de comprobante.',
            'tipo_comprobante.in'         => 'El tipo de comprobante seleccionado no es válido.',
            'descuento.min'               => 'El descuento no puede ser un valor negativo.',
            'porcentaje_descuento.max'    => 'El porcentaje de descuento no puede ser mayor al 100%.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'tipo_comprobante'     => $this->filled('tipo_comprobante') ? trim($this->input('tipo_comprobante')) : 'ticket',
            'metodo_pago'          => $this->filled('metodo_pago') ? trim($this->input('metodo_pago')) : 'efectivo',
            'monto_recibido'       => $this->filled('monto_recibido') ? (float) $this->input('monto_recibido') : null,
            'tipo_descuento'       => $this->filled('tipo_descuento') ? trim($this->input('tipo_descuento')) : 'monto',
            'porcentaje_descuento' => $this->filled('porcentaje_descuento') ? max(0, (float) $this->input('porcentaje_descuento')) : 0,
            'receta_modalidad'     => $this->filled('receta_modalidad') ? trim($this->input('receta_modalidad')) : 'sin_receta',
            'receta_omision_motivo'=> $this->filled('receta_omision_motivo') ? trim($this->input('receta_omision_motivo')) : null,
            'serie'                => $this->filled('serie') ? trim($this->input('serie')) : null,
            'numero_comprobante'   => $this->filled('numero_comprobante') ? trim($this->input('numero_comprobante')) : null,
            'referencia_pago'      => $this->filled('referencia_pago') ? trim($this->input('referencia_pago')) : null,
            'observaciones'        => $this->filled('observaciones') ? trim($this->input('observaciones')) : null,
            'descuento'            => $this->filled('descuento') ? max(0, (float) $this->input('descuento')) : 0,
            'cliente_id'           => $this->filled('cliente_id') ? (int) $this->input('cliente_id') : null,
        ]);
    }
}
