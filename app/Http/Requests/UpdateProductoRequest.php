<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('editar productos');
    }

    public function rules(): array
    {
        $productoId = $this->route('producto')?->id ?? $this->route('producto');

        return [
            'codigo_barra' => ['nullable', 'string', 'max:50', Rule::unique('productos', 'codigo_barra')->ignore($productoId)],
            'nombre' => ['required', 'string', 'max:150'],
            'principio_activo' => ['nullable', 'string', 'max:200'],
            'concentracion' => ['nullable', 'string', 'max:100'],
            'forma_farmaceutica' => ['nullable', 'string', 'max:100'],
            'categoria_id' => ['required', 'integer', 'exists:categorias,id'],
            'laboratorio_id' => ['nullable', 'integer', 'exists:laboratorios,id'],
            'registro_sanitario' => ['nullable', 'string', 'max:100'],
            'tipo_control' => ['required', 'in:venta_libre,receta_medica,receta_retenida'],
            'precio_compra' => ['nullable', 'numeric', 'min:0'],
            'precio_venta' => ['required', 'numeric', 'min:0'],
            'stock_minimo' => ['nullable', 'integer', 'min:0'],
            'ubicacion' => ['nullable', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string'],
            'imagen' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'requiere_receta' => ['nullable', 'boolean'],
            'activo' => ['nullable', 'boolean'],
            'presentaciones' => ['nullable', 'array'],
            'presentaciones.*.nombre' => ['nullable', 'string', 'max:100'],
            'presentaciones.*.unidades_por_presentacion' => ['nullable', 'integer', 'min:1'],
            'presentaciones.*.precio_compra' => ['nullable', 'numeric', 'min:0'],
            'presentaciones.*.precio_venta' => ['nullable', 'numeric', 'min:0'],
            'presentaciones.*.codigo_barras' => ['nullable', 'string', 'max:50'],
            'presentaciones.*.es_unidad_base' => ['nullable'],
        ];
    }
}
