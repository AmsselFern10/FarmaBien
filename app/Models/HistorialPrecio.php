<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class HistorialPrecio extends Model
{
    protected $table = 'historial_precios';

    protected $fillable = [
        'producto_id',
        'proveedor_id',
        'compra_id',
        'presentacion_id',
        'tipo_presentacion',
        'unidades_por_presentacion',
        'precio_compra',
        'precio_unitario_base',
        'tipo',
        'fecha',
        'observaciones',
    ];

    protected $casts = [
        'unidades_por_presentacion' => 'integer',
        'precio_compra'             => 'decimal:4',
        'precio_unitario_base'      => 'decimal:4',
        'fecha'                     => 'datetime',
    ];

    // Relaciones
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function compra(): BelongsTo
    {
        return $this->belongsTo(Compra::class);
    }

    public function presentacion(): BelongsTo
    {
        return $this->belongsTo(PresentacionProducto::class, 'presentacion_id');
    }

    // Scopes
    public function scopeCompras($query)
    {
        return $query->where('tipo', 'compra');
    }

    public function scopeCotizaciones($query)
    {
        return $query->where('tipo', 'cotizacion');
    }

    /**
     * Obtener el mejor precio unitario base registrado para un producto (de cualquier proveedor activo).
     */
    public static function mejorPrecioParaProducto(int $productoId)
    {
        return self::where('producto_id', $productoId)
            ->whereHas('proveedor', fn($q) => $q->where('activo', true))
            ->orderBy('precio_unitario_base', 'asc')
            ->first();
    }

    /**
     * Obtener el último precio registrado para un producto y proveedor específicos.
     */
    public static function ultimoPrecio(int $productoId, int $proveedorId)
    {
        return self::where('producto_id', $productoId)
            ->where('proveedor_id', $proveedorId)
            ->orderBy('fecha', 'desc')
            ->orderBy('id', 'desc')
            ->first();
    }

    /**
     * Obtener la comparativa completa de proveedores para un producto.
     */
    public static function comparativaPorProducto(int $productoId)
    {
        $historiales = self::with(['proveedor', 'presentacion', 'compra'])
            ->where('producto_id', $productoId)
            ->orderBy('fecha', 'desc')
            ->get();

        // Agrupar por proveedor obteniendo la última cotización/compra y el mejor precio histórico
        $porProveedor = [];

        foreach ($historiales as $h) {
            $provId = $h->proveedor_id;
            if (!isset($porProveedor[$provId])) {
                $porProveedor[$provId] = [
                    'proveedor'            => $h->proveedor,
                    'ultimo_registro'      => $h,
                    'ultimo_precio_base'   => (float)$h->precio_unitario_base,
                    'mejor_precio_base'    => (float)$h->precio_unitario_base,
                    'mejor_registro'       => $h,
                    'total_operaciones'    => 0,
                    'registros'            => collect(),
                ];
            }

            $porProveedor[$provId]['total_operaciones']++;
            $porProveedor[$provId]['registros']->push($h);

            if ((float)$h->precio_unitario_base < $porProveedor[$provId]['mejor_precio_base']) {
                $porProveedor[$provId]['mejor_precio_base'] = (float)$h->precio_unitario_base;
                $porProveedor[$provId]['mejor_registro'] = $h;
            }
        }

        return collect($porProveedor)->sortBy('ultimo_precio_base')->values();
    }
}
