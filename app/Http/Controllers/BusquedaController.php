<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Cliente;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BusquedaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Endpoint de búsqueda global: productos, clientes y ventas.
     * GET /busqueda?q=texto
     * Retorna JSON con máx 5 resultados por categoría.
     */
    public function global(Request $request)
    {
        $q = trim($request->get('q', ''));

        if (strlen($q) < 2) {
            return response()->json(['productos' => [], 'clientes' => [], 'ventas' => []]);
        }

        $like = '%' . $q . '%';

        // ── Productos ──────────────────────────────────────────────────────
        $productos = Producto::select('id', 'nombre', 'principio_activo', 'codigo_barra',
                                      'precio_venta', 'activo', 'nivel_controlado')
            ->where(function ($w) use ($like) {
                $w->where('nombre', 'like', $like)
                  ->orWhere('principio_activo', 'like', $like)
                  ->orWhere('codigo_barra', 'like', $like);
            })
            ->limit(5)
            ->get()
            ->map(fn ($p) => [
                'id'               => $p->id,
                'nombre'           => $p->nombre,
                'subtitulo'        => $p->principio_activo ?: $p->codigo_barra,
                'precio'           => number_format($p->precio_venta, 2),
                'activo'           => $p->activo,
                'nivel_controlado' => $p->nivel_controlado,
                'url'              => route('productos.show', $p->id),
            ]);

        // ── Clientes ───────────────────────────────────────────────────────
        $clientes = Cliente::select('id', 'nombre', 'cedula', 'telefono', 'activo')
            ->where(function ($w) use ($like) {
                $w->where('nombre', 'like', $like)
                  ->orWhere('cedula', 'like', $like)
                  ->orWhere('telefono', 'like', $like);
            })
            ->limit(5)
            ->get()
            ->map(fn ($c) => [
                'id'       => $c->id,
                'nombre'   => $c->nombre,
                'subtitulo'=> $c->cedula ?: $c->telefono ?: '—',
                'activo'   => $c->activo,
                'url'      => route('clientes.show', $c->id),
            ]);

        // ── Ventas ─────────────────────────────────────────────────────────
        // Buscar por ID numérico o por nombre del cliente
        $ventasQuery = Venta::select('ventas.id', 'ventas.total', 'ventas.estado',
                                     'ventas.fecha', 'ventas.metodo_pago',
                                     'clientes.nombre as cliente_nombre')
            ->leftJoin('clientes', 'ventas.cliente_id', '=', 'clientes.id')
            ->limit(5);

        if (is_numeric($q)) {
            $ventasQuery->where('ventas.id', (int) $q);
        } else {
            $ventasQuery->where('clientes.nombre', 'like', $like);
        }

        $ventas = $ventasQuery->orderByDesc('ventas.id')->get()
            ->map(fn ($v) => [
                'id'       => $v->id,
                'nombre'   => 'Venta #' . $v->id,
                'subtitulo'=> ($v->cliente_nombre ?? 'Sin cliente') . ' — C$ ' . number_format($v->total, 2),
                'estado'   => $v->estado,
                'url'      => route('ventas.show', $v->id),
            ]);

        return response()->json(compact('productos', 'clientes', 'ventas'));
    }
}
