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
        $productoId = $this->route('producto'); // ID del producto en la ruta

        return [
            'codigo_barra' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('productos')->ignore($productoId)
            ],
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string|max:500',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'categoria_id' => 'required|exists:categorias,id',
            'precio_venta' => 'required|numeric|min:0',
            'stock_minimo' => 'required|integer|min:0',
            'requiere_receta' => 'boolean',
            'activo' => 'boolean',
        ];
    }
}