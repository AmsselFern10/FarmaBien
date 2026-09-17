<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnularCompraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('anular compras');
    }

    public function rules(): array
    {
        return [
            'motivo' => ['required', 'string', 'min:5', 'max:255'],
        ];
    }
}
