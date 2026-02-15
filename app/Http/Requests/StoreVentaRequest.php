<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class StoreVentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Normaliza la fecha para que SIEMPRE incluya hora.
     * - Si viene como YYYY-MM-DD => se le agrega la hora actual.
     * - Si viene como datetime-local (YYYY-MM-DDTHH:MM) o parseable => se normaliza a Y-m-d H:i:s
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
            'cliente_id' => 'nullable|exists:clientes,id',
            'metodo_pago' => 'required|string|max:30',

            // Fecha con hora
            'fecha' => 'nullable|date_format:Y-m-d H:i:s',

            'observaciones' => 'nullable|string',

            //Campos de pago (caja)
            'monto_recibido' => 'nullable|numeric|min:0',
            'cambio' => 'nullable|numeric|min:0',
            'referencia_pago' => 'nullable|string|max:100',

            // Descuento global (%)
            'descuento' => 'nullable|numeric|min:0|max:100',

            'productos' => 'required|array|min:1',

            'productos.*.producto_id' => 'required|exists:productos,id',
            'productos.*.lote_id' => 'required|exists:lotes,id',

            // Presentación (opcional)
            'productos.*.presentacion_id' => 'nullable|exists:presentaciones_producto,id',
            'productos.*.cantidad_presentaciones' => 'nullable|integer|min:1',

            // Venta por unidad (si no hay presentación)
            'productos.*.cantidad' => 'nullable|integer|min:1',

            // Precio y descuento por producto
            'productos.*.precio_unitario' => 'nullable|numeric|min:0',
            'productos.*.descuento' => 'nullable|numeric|min:0|max:100',

            // Recetas
            'recetas' => 'nullable|array',
            'recetas.*' => 'exists:recetas,id',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            foreach ($this->input('productos', []) as $index => $item) {

                // Debe existir cantidad o cantidad_presentaciones
                if (
                    empty($item['cantidad']) &&
                    empty($item['cantidad_presentaciones'])
                ) {
                    $validator->errors()->add(
                        "productos.$index.cantidad",
                        'Debe ingresar cantidad o cantidad de presentaciones.'
                    );
                }

                // Si hay presentación, debe haber cantidad_presentaciones
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
