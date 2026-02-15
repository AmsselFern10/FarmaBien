<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class UpdateVentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Normaliza la fecha para que SIEMPRE incluya hora.
     */
    protected function prepareForValidation(): void
    {
        $fecha = $this->input('fecha');

        if ($fecha) {
            try {
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
                    $fecha = Carbon::createFromFormat('Y-m-d', $fecha, config('app.timezone'))
                        ->setTimeFromTimeString(now()->format('H:i:s'))
                        ->format('Y-m-d H:i:s');
                } else {
                    $fecha = Carbon::parse($fecha, config('app.timezone'))->format('Y-m-d H:i:s');
                }

                $this->merge(['fecha' => $fecha]);
            } catch (\Throwable $e) {
                // Deja que la validación marque error si el formato es inválido.
            }
        }
    }

    public function rules(): array
    {
        return [
            'motivo' => 'required|string|min:10',
            'cliente_id' => 'nullable|exists:clientes,id',
            'metodo_pago' => 'required|string|max:30',

            'observaciones' => 'nullable|string',

            // ✅ Si permites ajustar fecha en modificación (con hora)
            'fecha' => 'nullable|date_format:Y-m-d H:i:s',

            // ✅ Campos de pago (caja)
            'monto_recibido' => 'nullable|numeric|min:0',
            'cambio' => 'nullable|numeric|min:0',
            'referencia_pago' => 'nullable|string|max:100',

            // (Opcional para UI; la VentaService lo ignora si no existe columna)
            'banco' => 'nullable|string|max:30',

            // Descuento global (%)
            'descuento' => 'nullable|numeric|min:0|max:100',

            'productos' => 'required|array|min:1',

            'productos.*.producto_id' => 'required|exists:productos,id',
            'productos.*.lote_id' => 'required|exists:lotes,id',

            'productos.*.presentacion_id' => 'nullable|exists:presentaciones_producto,id',
            'productos.*.cantidad_presentaciones' => 'nullable|integer|min:1',
            'productos.*.cantidad' => 'nullable|integer|min:1',

            'productos.*.precio_unitario' => 'nullable|numeric|min:0',
            'productos.*.descuento' => 'nullable|numeric|min:0|max:100',

            'recetas' => 'nullable|array',
            'recetas.*' => 'exists:recetas,id',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            foreach ($this->input('productos', []) as $index => $item) {

                if (
                    empty($item['cantidad']) &&
                    empty($item['cantidad_presentaciones'])
                ) {
                    $validator->errors()->add(
                        "productos.$index.cantidad",
                        'Debe ingresar cantidad o cantidad de presentaciones.'
                    );
                }

                if (!empty($item['presentacion_id']) && empty($item['cantidad_presentaciones'])) {
                    $validator->errors()->add(
                        "productos.$index.cantidad_presentaciones",
                        'Debe indicar la cantidad de presentaciones.'
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'fecha.date_format' => 'La fecha debe incluir hora (formato: YYYY-MM-DD HH:MM:SS).',
        ];
    }
}
