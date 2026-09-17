<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AjusteInventarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('ajustar inventario');
    }

    public function rules(): array
    {
        return [
            'lote_id' => ['required', 'integer', 'exists:lotes,id'],
            'stock_nuevo' => ['required', 'integer', 'min:0'],
            'subtipo' => ['required', 'in:ajuste_manual,ajuste_positivo,ajuste_negativo,merma_vencimiento,merma_danio'],
            'motivo' => ['required', 'string', 'min:5', 'max:255'],
        ];
    }
}
