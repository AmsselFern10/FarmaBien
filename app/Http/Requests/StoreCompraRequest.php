<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use App\Models\PresentacionProducto;

class StoreCompraRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Debe calzar con tu middleware del controller:
        // $this->middleware('permission:registrar compras')->only(['create', 'store']);
        return $this->user()?->can('registrar compras') ?? false;
    }

    public function rules(): array
    {
        return [
            'proveedor_id' => 'required|exists:proveedores,id',
            'fecha' => 'nullable|date|before_or_equal:today',

            // NUEVOS CAMPOS
            'descuento' => 'nullable|numeric|min:0',
            'observaciones' => 'nullable|string|max:2000',

            // Detalle
            'productos' => 'required|array|min:1',

            'productos.*.producto_id' => 'required|exists:productos,id',

            // Presentación registrada (opcional)
            'productos.*.presentacion_id' => 'nullable|exists:presentaciones_producto,id',

            // Si es manual, esta puede venir con texto (opcional)
            'productos.*.tipo_presentacion' => 'nullable|string|max:60',

            // Cantidad por presentación y unidades por presentación
            'productos.*.cantidad_presentaciones' => 'required|integer|min:1',
            'productos.*.unidades_por_presentacion' => 'required|integer|min:1',

            // Precio unitario por unidad base
            'productos.*.precio_unitario' => 'required|numeric|min:0',

            // Lote / vencimiento
            'productos.*.numero_lote' => 'required|string|max:50',
            'productos.*.fecha_vencimiento' => 'required|date|after:today',
        ];
    }

    public function messages(): array
    {
        return [
            'proveedor_id.required' => 'Debe seleccionar un proveedor.',
            'proveedor_id.exists' => 'El proveedor seleccionado no existe.',
            'fecha.date' => 'La fecha debe ser válida.',
            'fecha.before_or_equal' => 'La fecha de compra no puede ser futura.',

            // NUEVOS CAMPOS
            'descuento.numeric' => 'El descuento debe ser un número válido.',
            'descuento.min' => 'El descuento no puede ser negativo.',
            'observaciones.string' => 'Las observaciones deben ser texto.',
            'observaciones.max' => 'Las observaciones no pueden exceder 2000 caracteres.',

            'productos.required' => 'Debe agregar al menos un producto.',
            'productos.array' => 'El detalle de productos no es válido.',
            'productos.min' => 'Debe agregar al menos un producto.',

            'productos.*.producto_id.required' => 'El producto es obligatorio.',
            'productos.*.producto_id.exists' => 'El producto seleccionado no existe.',

            'productos.*.presentacion_id.exists' => 'La presentación seleccionada no existe.',

            'productos.*.cantidad_presentaciones.required' => 'La cantidad es obligatoria.',
            'productos.*.cantidad_presentaciones.min' => 'La cantidad debe ser al menos 1.',

            'productos.*.unidades_por_presentacion.required' => 'Las unidades por presentación son obligatorias.',
            'productos.*.unidades_por_presentacion.min' => 'Las unidades por presentación deben ser al menos 1.',

            'productos.*.precio_unitario.required' => 'El precio unitario es obligatorio.',
            'productos.*.precio_unitario.min' => 'El precio debe ser mayor o igual a 0.',

            'productos.*.numero_lote.required' => 'El número de lote es obligatorio.',
            'productos.*.numero_lote.max' => 'El número de lote no puede exceder 50 caracteres.',

            'productos.*.fecha_vencimiento.required' => 'La fecha de vencimiento es obligatoria.',
            'productos.*.fecha_vencimiento.after' => 'La fecha de vencimiento debe ser futura.',
        ];
    }

    public function attributes(): array
    {
        return [
            'proveedor_id' => 'proveedor',
            'fecha' => 'fecha de compra',

            // NUEVOS CAMPOS
            'descuento' => 'descuento',
            'observaciones' => 'observaciones',

            'productos' => 'productos',
            'productos.*.producto_id' => 'producto',
            'productos.*.presentacion_id' => 'presentación',
            'productos.*.tipo_presentacion' => 'presentación manual',
            'productos.*.cantidad_presentaciones' => 'cantidad',
            'productos.*.unidades_por_presentacion' => 'unidades por presentación',
            'productos.*.precio_unitario' => 'precio unitario',
            'productos.*.numero_lote' => 'número de lote',
            'productos.*.fecha_vencimiento' => 'fecha de vencimiento',
        ];
    }

    public function withValidator($validator): void
    {
        /** @var Validator $validator */
        $validator->after(function (Validator $validator) {
            $productos = $this->input('productos', []);

            foreach ($productos as $index => $item) {
                $productoId = $item['producto_id'] ?? null;
                $presentacionId = $item['presentacion_id'] ?? null;
                $unidades = (int)($item['unidades_por_presentacion'] ?? 0);

                // Si hay presentacion_id, validar que pertenece al producto y que está activa
                if (!empty($presentacionId)) {
                    $presentacion = PresentacionProducto::find($presentacionId);

                    if ($presentacion && (int)$presentacion->producto_id !== (int)$productoId) {
                        $validator->errors()->add(
                            "productos.{$index}.presentacion_id",
                            'La presentación no corresponde al producto seleccionado.'
                        );
                    }

                    if ($presentacion && !$presentacion->activo) {
                        $validator->errors()->add(
                            "productos.{$index}.presentacion_id",
                            'La presentación seleccionada no está activa.'
                        );
                    }
                }

                // Coherencia: si NO hay presentacion_id, es unidad base → unidades debe ser 1
                if (empty($presentacionId) && $unidades !== 1) {
                    $validator->errors()->add(
                        "productos.{$index}.unidades_por_presentacion",
                        'Para unidad base, las unidades por presentación deben ser 1.'
                    );
                }

                // Manual: si NO hay presentacion_id y unidades > 1, exigir texto de presentación manual
                // (esto hace tu UI más consistente)
                if (empty($presentacionId) && $unidades > 1 && empty($item['tipo_presentacion'])) {
                    $validator->errors()->add(
                        "productos.{$index}.tipo_presentacion",
                        'Si usas unidades por presentación > 1 sin una presentación registrada, debes indicar la presentación manual.'
                    );
                }
            }
        });
    }

    protected function prepareForValidation(): void
    {
        // Si productos viene como JSON string, decodificarlo (compatibilidad con tu JS)
        if ($this->has('productos') && is_string($this->input('productos'))) {
            $this->merge([
                'productos' => json_decode($this->input('productos'), true) ?: [],
            ]);
        }

        $productos = $this->input('productos', []);

        foreach ($productos as $index => $item) {
            // Compatibilidad: si viene cantidad (viejo), mapear a cantidad_presentaciones
            if (!isset($item['cantidad_presentaciones']) && isset($item['cantidad'])) {
                $productos[$index]['cantidad_presentaciones'] = $item['cantidad'];
            }

            // Si no trae presentacion_id, forzar unidad base por defecto
            if (empty($item['presentacion_id'])) {
                $productos[$index]['presentacion_id'] = null;

                // Si no viene unidades_por_presentacion, default 1
                if (!isset($item['unidades_por_presentacion'])) {
                    $productos[$index]['unidades_por_presentacion'] = 1;
                }
            }
        }

        $this->merge(['productos' => $productos]);
    }
}