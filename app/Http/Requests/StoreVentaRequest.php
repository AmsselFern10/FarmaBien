<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVentaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('realizar ventas');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Cliente (opcional - puede ser público general)
            'cliente_id' => 'nullable|exists:clientes,id',
            
            // Método de pago
            'metodo_pago' => 'required|string|in:efectivo,tarjeta,transferencia,yape,plin',
            
            // Fecha de venta (opcional, por defecto hoy)
            'fecha' => 'nullable|date|before_or_equal:today',
            
            // Productos (mínimo 1)
            'productos' => 'required|array|min:1',
            'productos.*.producto_id' => 'required|exists:productos,id',
            'productos.*.lote_id' => 'required|exists:lotes,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio_unitario' => 'required|numeric|min:0',
            'productos.*.descuento' => 'nullable|numeric|min:0|max:100',
            
            // Recetas médicas (opcional - solo para productos controlados)
            'recetas' => 'nullable|array',
            'recetas.*' => 'exists:recetas,id',
            
            // Observaciones adicionales
            'observaciones' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'cliente_id.exists' => 'El cliente seleccionado no existe.',
            
            'metodo_pago.required' => 'Debe seleccionar un método de pago.',
            'metodo_pago.in' => 'El método de pago seleccionado no es válido.',
            
            'fecha.date' => 'La fecha no es válida.',
            'fecha.before_or_equal' => 'La fecha de venta no puede ser futura.',
            
            'productos.required' => 'Debe agregar al menos un producto a la venta.',
            'productos.min' => 'Debe agregar al menos un producto a la venta.',
            'productos.array' => 'El formato de productos no es válido.',
            
            'productos.*.producto_id.required' => 'El producto es obligatorio.',
            'productos.*.producto_id.exists' => 'Uno de los productos seleccionados no existe.',
            
            'productos.*.lote_id.required' => 'Debe seleccionar un lote para cada producto.',
            'productos.*.lote_id.exists' => 'Uno de los lotes seleccionados no existe o está agotado.',
            
            'productos.*.cantidad.required' => 'La cantidad es obligatoria.',
            'productos.*.cantidad.integer' => 'La cantidad debe ser un número entero.',
            'productos.*.cantidad.min' => 'La cantidad debe ser al menos 1.',
            
            'productos.*.precio_unitario.required' => 'El precio unitario es obligatorio.',
            'productos.*.precio_unitario.numeric' => 'El precio debe ser un valor numérico.',
            'productos.*.precio_unitario.min' => 'El precio debe ser mayor o igual a 0.',
            
            'productos.*.descuento.numeric' => 'El descuento debe ser un valor numérico.',
            'productos.*.descuento.min' => 'El descuento no puede ser negativo.',
            'productos.*.descuento.max' => 'El descuento no puede ser mayor al 100%.',
            
            'recetas.array' => 'El formato de recetas no es válido.',
            'recetas.*.exists' => 'Una de las recetas médicas seleccionadas no existe.',
            
            'observaciones.max' => 'Las observaciones no pueden exceder 500 caracteres.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'cliente_id' => 'cliente',
            'metodo_pago' => 'método de pago',
            'fecha' => 'fecha de venta',
            'productos' => 'productos',
            'productos.*.producto_id' => 'producto',
            'productos.*.lote_id' => 'lote',
            'productos.*.cantidad' => 'cantidad',
            'productos.*.precio_unitario' => 'precio unitario',
            'productos.*.descuento' => 'descuento',
            'recetas' => 'recetas médicas',
            'observaciones' => 'observaciones',
        ];
    }

    /**
     * Preparar datos para validación
     */
    protected function prepareForValidation()
    {
        // Si productos viene como JSON string, decodificarlo
        if ($this->has('productos') && is_string($this->productos)) {
            $this->merge([
                'productos' => json_decode($this->productos, true)
            ]);
        }

        // Si recetas viene como JSON string, decodificarlo
        if ($this->has('recetas') && is_string($this->recetas)) {
            $this->merge([
                'recetas' => json_decode($this->recetas, true)
            ]);
        }

        // Si no se especifica fecha, usar la actual
        if (!$this->has('fecha')) {
            $this->merge([
                'fecha' => now()->toDateString()
            ]);
        }
    }

    /**
     * Validación adicional después de las reglas básicas
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validar que los productos con receta requerida tengan recetas asociadas
            if ($this->has('productos')) {
                foreach ($this->productos as $index => $item) {
                    if (isset($item['producto_id'])) {
                        $producto = \App\Models\Producto::find($item['producto_id']);
                        
                        if ($producto && $producto->requiere_receta) {
                            if (empty($this->recetas)) {
                                $validator->errors()->add(
                                    'recetas',
                                    "El producto '{$producto->nombre}' requiere receta médica."
                                );
                            }
                        }
                    }
                }
            }

            // Validar stock disponible en lotes
            if ($this->has('productos')) {
                foreach ($this->productos as $index => $item) {
                    if (isset($item['lote_id']) && isset($item['cantidad'])) {
                        $lote = \App\Models\Lote::find($item['lote_id']);
                        
                        if ($lote && $lote->stock_actual < $item['cantidad']) {
                            $validator->errors()->add(
                                "productos.{$index}.cantidad",
                                "Stock insuficiente. Disponible: {$lote->stock_actual}"
                            );
                        }
                    }
                }
            }
        });
    }
}