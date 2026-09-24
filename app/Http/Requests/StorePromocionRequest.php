<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePromocionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('crear promociones');
    }

    public function rules(): array
    {
        return [
            'nombre'          => ['required', 'string', 'max:150'],
            'descripcion'     => ['nullable', 'string', 'max:1000'],
            'tipo'            => ['required', 'string', 'in:porcentaje,monto_fijo,2x1,3x2'],
            'valor'           => ['required', 'numeric', 'min:0'],
            'alcance'         => ['required', 'string', 'in:producto,categoria,laboratorio,general'],
            'producto_id'     => ['nullable', 'required_if:alcance,producto', 'exists:productos,id'],
            'categoria_id'    => ['nullable', 'required_if:alcance,categoria', 'exists:categorias,id'],
            'laboratorio_id'  => ['nullable', 'required_if:alcance,laboratorio', 'exists:laboratorios,id'],
            'fecha_inicio'    => ['required', 'date'],
            'fecha_fin'       => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'min_unidades'    => ['required', 'integer', 'min:1'],
            'stock_limite'    => ['nullable', 'integer', 'min:1'],
            'activo'          => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required'               => 'El nombre de la promoción es obligatorio.',
            'tipo.required'                 => 'Debe seleccionar el tipo de descuento o promoción.',
            'valor.required'                => 'El valor de la promoción es requerido.',
            'alcance.required'              => 'Debe definir el alcance de la promoción.',
            'producto_id.required_if'       => 'Debe seleccionar un producto cuando el alcance es por producto.',
            'categoria_id.required_if'      => 'Debe seleccionar una categoría cuando el alcance es por categoría.',
            'laboratorio_id.required_if'    => 'Debe seleccionar un laboratorio cuando el alcance es por laboratorio.',
            'fecha_inicio.required'         => 'La fecha y hora de inicio es obligatoria.',
            'fecha_fin.required'            => 'La fecha y hora de finalización es obligatoria.',
            'fecha_fin.after_or_equal'      => 'La fecha de fin debe ser posterior o igual a la fecha de inicio.',
        ];
    }
}
