<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Cliente;
use App\Models\Venta;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('q', '');
        
        if (strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        $results = collect();

        // Buscar productos
        if (auth()->user()->can('ver productos')) {
            $productos = Producto::where('nombre', 'LIKE', "%{$query}%")
                ->orWhere('codigo_barras', 'LIKE', "%{$query}%")
                ->limit(5)
                ->get()
                ->map(fn($p) => [
                    'id' => $p->id,
                    'title' => $p->nombre,
                    'description' => "Stock: {$p->stock} | Precio: $" . number_format($p->precio_venta, 2),
                    'type' => 'Producto',
                    'icon' => '💊',
                    'url' => route('productos.show', $p),
                ]);
            
            $results = $results->concat($productos);
        }

        // Buscar clientes
        if (auth()->user()->can('ver clientes')) {
            $clientes = Cliente::where('nombre', 'LIKE', "%{$query}%")
                ->orWhere('cedula', 'LIKE', "%{$query}%")
                ->limit(5)
                ->get()
                ->map(fn($c) => [
                    'id' => $c->id,
                    'title' => $c->nombre,
                    'description' => $c->cedula ? "Cédula: {$c->cedula}" : $c->email,
                    'type' => 'Cliente',
                    'icon' => '👤',
                    'url' => route('clientes.show', $c),
                ]);
            
            $results = $results->concat($clientes);
        }

        // Buscar ventas
        if (auth()->user()->can('ver ventas')) {
            $ventas = Venta::where('numero_factura', 'LIKE', "%{$query}%")
                ->limit(5)
                ->get()
                ->map(fn($v) => [
                    'id' => $v->id,
                    'title' => "Venta #{$v->numero_factura}",
                    'description' => "Total: $" . number_format($v->total, 2) . " | " . $v->created_at->format('d/m/Y'),
                    'type' => 'Venta',
                    'icon' => '🧾',
                    'url' => route('ventas.show', $v),
                ]);
            
            $results = $results->concat($ventas);
        }

        return response()->json([
            'results' => $results->take(10)->values()
        ]);
    }
}