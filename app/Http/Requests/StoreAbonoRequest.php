<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAbonoRequest extends FormRequest
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
        $compra = $this->route('compra');
        $maxMonto = $compra ? (float) $compra->saldo_pendiente : 999999999;

        return [
            'monto'              => ['required', 'numeric', 'min:0.01', "max:{$maxMonto}"],
            'metodo_pago'        => ['required', 'in:efectivo,transferencia,cheque,otro'],
            'banco'              => ['nullable', 'string', 'max:100'],
            'numero_referencia'  => ['nullable', 'string', 'max:100'],
            'fecha_pago'         => ['nullable', 'date'],
            'observaciones'      => ['nullable', 'string', 'max:500'],
            'registrar_en_caja'  => ['nullable', 'boolean'],
        ];
    }

    /**
     * Custom messages in Spanish.
     */
    public function messages(): array
    {
        return [
            'monto.required'    => 'El monto a abonar es obligatorio.',
            'monto.numeric'     => 'El monto debe ser un valor numérico válido.',
            'monto.min'         => 'El monto a abonar debe ser de al menos C$ 0.01.',
            'monto.max'         => 'El monto a abonar no puede exceder el saldo pendiente de la cuenta por pagar.',
            'metodo_pago.required' => 'El método de pago es obligatorio.',
            'metodo_pago.in'    => 'El método de pago seleccionado no es válido.',
            'fecha_pago.date'   => 'La fecha de pago debe ser una fecha válida.',
            'observaciones.max' => 'Las observaciones no pueden superar los 500 caracteres.',
        ];
    }

    /**
     * Custom attributes in Spanish.
     */
    public function attributes(): array
    {
        return [
            'monto'             => 'monto del abono',
            'metodo_pago'       => 'método de pago',
            'banco'             => 'banco',
            'numero_referencia' => 'número de referencia',
            'fecha_pago'        => 'fecha de pago',
            'observaciones'     => 'observaciones',
            'registrar_en_caja' => 'registro en caja',
        ];
    }
}
