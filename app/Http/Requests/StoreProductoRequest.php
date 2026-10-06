<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('crear productos');
    }

    public function rules(): array
    {
        return [
            'codigo_barra' => ['nullable', 'string', 'max:50', 'unique:productos,codigo_barra'],
            'nombre' => ['required', 'string', 'max:150'],
            'principio_activo' => ['nullable', 'string', 'max:200'],
            'concentracion' => ['nullable', 'string', 'max:100'],
            'forma_farmaceutica' => ['nullable', 'string', 'max:100'],
            'categoria_id' => ['required', 'integer', 'exists:categorias,id'],
            'laboratorio_id' => ['nullable', 'integer', 'exists:laboratorios,id'],
            'registro_sanitario' => ['nullable', 'string', 'max:100'],
            'tipo_control' => ['required', 'string', 'in:venta_libre,controlado'],
            'precio_compra' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'precio_venta' => ['required', 'numeric', 'min:0.01', 'max:9999999.99'],
            'stock_minimo' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'ubicacion' => ['nullable', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'imagen' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'requiere_receta' => ['nullable', 'boolean'],
            'activo' => ['nullable', 'boolean'],
            'presentaciones' => ['nullable', 'array'],
            'presentaciones.*.id' => ['nullable', 'integer'],
            'presentaciones.*.nombre' => ['nullable', 'string', 'max:100'],
            'presentaciones.*.unidades_por_presentacion' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'presentaciones.*.precio_compra' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'presentaciones.*.precio_venta' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'presentaciones.*.codigo_barras' => ['nullable', 'string', 'max:50'],
            'presentaciones.*.es_unidad_base' => ['nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del medicamento es obligatorio.',
            'nombre.max' => 'El nombre no puede superar 150 caracteres.',
            'categoria_id.required' => 'Debes seleccionar una categoría.',
            'categoria_id.exists' => 'La categoría seleccionada no es válida.',
            'laboratorio_id.exists' => 'El laboratorio seleccionado no es válido.',
            'precio_venta.required' => 'El precio de venta es obligatorio.',
            'precio_venta.min' => 'El precio de venta debe ser mayor a 0.',
            'codigo_barra.unique' => 'El código de barras ingresado ya está registrado en otro producto.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $codigoBarra = trim((string)$this->input('codigo_barra', ''));
        $registroSanitario = trim((string)$this->input('registro_sanitario', ''));
        $ubicacion = trim((string)$this->input('ubicacion', ''));
        $principioActivo = trim((string)$this->input('principio_activo', ''));
        $concentracion = trim((string)$this->input('concentracion', ''));
        $formaFarmaceutica = trim((string)$this->input('forma_farmaceutica', ''));
        $nombre = trim((string)$this->input('nombre', ''));

        $this->merge([
            'nombre' => $nombre,
            'codigo_barra' => $codigoBarra !== '' ? $codigoBarra : null,
            'registro_sanitario' => $registroSanitario !== '' ? $registroSanitario : null,
            'ubicacion' => $ubicacion !== '' ? $ubicacion : null,
            'principio_activo' => $principioActivo !== '' ? $principioActivo : null,
            'concentracion' => $concentracion !== '' ? $concentracion : null,
            'forma_farmaceutica' => $formaFarmaceutica !== '' ? $formaFarmaceutica : null,
            'precio_compra' => $this->filled('precio_compra') ? (float)$this->input('precio_compra') : 0,
            'precio_venta' => $this->filled('precio_venta') ? (float)$this->input('precio_venta') : null,
            'stock_minimo' => $this->filled('stock_minimo') ? (int)$this->input('stock_minimo') : 0,
            'requiere_receta' => $this->input('tipo_control') === 'controlado' || $this->boolean('requiere_receta'),
            'activo' => $this->has('activo') ? $this->boolean('activo') : true,
        ]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $presentaciones = $this->input('presentaciones', []);
            $codigos = [];
            $prodCodigo = $this->input('codigo_barra');

            if (!empty($prodCodigo)) {
                $existeEnPresentacion = \App\Models\PresentacionProducto::where('codigo_barras', $prodCodigo)->exists();
                if ($existeEnPresentacion) {
                    $v->errors()->add('codigo_barra', "El código de barra '{$prodCodigo}' ya está asignado a una presentación de otro medicamento.");
                }
            }

            foreach ($presentaciones as $idx => $p) {
                $cb = !empty($p['codigo_barras']) ? trim($p['codigo_barras']) : null;
                if (!$cb) continue;

                if (in_array($cb, $codigos)) {
                    $v->errors()->add("presentaciones.{$idx}.codigo_barras", "El código de barra '{$cb}' está duplicado entre las presentaciones de este producto.");
                }
                $codigos[] = $cb;

                if (\App\Models\Producto::where('codigo_barra', $cb)->exists()) {
                    $v->errors()->add("presentaciones.{$idx}.codigo_barras", "El código de barra '{$cb}' ya está registrado como código principal de otro medicamento.");
                }

                if (\App\Models\PresentacionProducto::where('codigo_barras', $cb)->exists()) {
                    $v->errors()->add("presentaciones.{$idx}.codigo_barras", "El código de barra '{$cb}' ya está asignado a otra presentación comercial existente.");
                }
            }
        });
    }
}
