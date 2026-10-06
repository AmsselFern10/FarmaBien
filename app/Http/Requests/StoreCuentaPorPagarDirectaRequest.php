<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCuentaPorPagarDirectaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('registrar compras');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'proveedor_id'           => ['nullable', 'integer', 'exists:proveedores,id'],
            'proveedor_nombre'       => ['nullable', 'string', 'max:150'],
            'numero_comprobante'     => ['required', 'string', 'max:50'],
            'fecha'                  => ['required', 'date'],
            'total'                  => ['required', 'numeric', 'min:0.01'],
            'dias_credito'           => ['nullable', 'integer', 'min:1', 'max:365'],
            'fecha_vencimiento_pago' => ['nullable', 'date', 'after_or_equal:fecha'],
            'concepto'               => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (!$this->filled('proveedor_id') && !$this->filled('proveedor_nombre')) {
                $validator->errors()->add('proveedor_id', 'Debe seleccionar un proveedor existente o ingresar un nombre de proveedor/acreedor.');
            }
        });
    }

    /**
     * Custom messages in Spanish.
     */
    public function messages(): array
    {
        return [
            'numero_comprobante.required'     => 'El número de factura o comprobante es obligatorio.',
            'fecha.required'                  => 'La fecha de emisión es obligatoria.',
            'fecha.date'                      => 'La fecha de emisión no tiene un formato válido.',
            'total.required'                  => 'El monto total de la factura es obligatorio.',
            'total.numeric'                   => 'El monto total debe ser un valor numérico válido.',
            'total.min'                       => 'El monto total debe ser de al menos C$ 0.01.',
            'dias_credito.min'                => 'Los días de crédito deben ser al menos 1 día.',
            'dias_credito.max'                => 'Los días de crédito no pueden exceder 365 días.',
            'fecha_vencimiento_pago.after_or_equal' => 'La fecha de vencimiento no puede ser anterior a la fecha de emisión.',
        ];
    }

    /**
     * Custom attributes in Spanish.
     */
    public function attributes(): array
    {
        return [
            'proveedor_id'           => 'proveedor',
            'proveedor_nombre'       => 'nombre del proveedor',
            'numero_comprobante'     => 'número de factura',
            'fecha'                  => 'fecha de emisión',
            'total'                  => 'monto total',
            'dias_credito'           => 'días de crédito',
            'fecha_vencimiento_pago' => 'fecha de vencimiento',
            'concepto'               => 'concepto del gasto',
        ];
    }
}
