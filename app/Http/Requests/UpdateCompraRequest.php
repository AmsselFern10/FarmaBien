<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use App\Models\PresentacionProducto;

class UpdateCompraRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Calza con tu middleware:
        // $this->middleware('permission:anular compras')->only(['edit', 'update', 'anular']);
        return $this->user()?->can('anular compras') ?? false;
    }

    public function rules(): array
    {
        return [
            // motivo para modificar (tu controller lo separa con except('motivo'))
            'motivo' => 'required|string|min:10|max:200',

            'proveedor_id' => 'required|exists:proveedores,id',
            'fecha' => 'nullable|date|before_or_equal:today',

            // NUEVOS CAMPOS
            'descuento' => 'nullable|numeric|min:0',
            'observaciones' => 'nullable|string|max:2000',

            'productos' => 'required|array|min:1',
            'productos.*.producto_id' => 'required|exists:productos,id',

            'productos.*.presentacion_id' => 'nullable|exists:presentaciones_producto,id',
            'productos.*.tipo_presentacion' => 'nullable|string|max:60',

            'productos.*.cantidad_presentaciones' => 'required|integer|min:1',
            'productos.*.unidades_por_presentacion' => 'required|integer|min:1',
            'productos.*.precio_unitario' => 'required|numeric|min:0',

            'productos.*.numero_lote' => 'required|string|max:50',
            'productos.*.fecha_vencimiento' => 'required|date|after:today',
        ];
    }

    public function messages(): array
    {
        return [
            'motivo.required' => 'Debe indicar el motivo de la modificación.',
            'motivo.min' => 'El motivo debe tener al menos 10 caracteres.',
            'motivo.max' => 'El motivo no puede exceder 200 caracteres.',

            'proveedor_id.required' => 'Debe seleccionar un proveedor.',
            'proveedor_id.exists' => 'El proveedor seleccionado no existe.',
            'fecha.before_or_equal' => 'La fecha de compra no puede ser futura.',

            // NUEVOS CAMPOS
            'descuento.numeric' => 'El descuento debe ser un número válido.',
            'descuento.min' => 'El descuento no puede ser negativo.',
            'observaciones.string' => 'Las observaciones deben ser texto.',
            'observaciones.max' => 'Las observaciones no pueden exceder 2000 caracteres.',

            'productos.required' => 'Debe agregar al menos un producto.',
            'productos.min' => 'Debe agregar al menos un producto.',
        ];
    }

    public function attributes(): array
    {
        return [
            'motivo' => 'motivo',
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

                if (empty($presentacionId) && $unidades !== 1) {
                    $validator->errors()->add(
                        "productos.{$index}.unidades_por_presentacion",
                        'Para unidad base, las unidades por presentación deben ser 1.'
                    );
                }

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
        if ($this->has('productos') && is_string($this->input('productos'))) {
            $this->merge([
                'productos' => json_decode($this->input('productos'), true) ?: [],
            ]);
        }

        $productos = $this->input('productos', []);

        foreach ($productos as $index => $item) {
            if (!isset($item['cantidad_presentaciones']) && isset($item['cantidad'])) {
                $productos[$index]['cantidad_presentaciones'] = $item['cantidad'];
            }

            if (empty($item['presentacion_id'])) {
                $productos[$index]['presentacion_id'] = null;

                if (!isset($item['unidades_por_presentacion'])) {
                    $productos[$index]['unidades_por_presentacion'] = 1;
                }
            }
        }

        $this->merge(['productos' => $productos]);
    }
}