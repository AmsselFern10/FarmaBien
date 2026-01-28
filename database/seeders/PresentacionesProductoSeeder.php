<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Producto;
use App\Models\PresentacionProducto;
use App\Models\Categoria;

class PresentacionesProductoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Presentaciones comunes según el tipo de producto
        
        // 1. TABLETAS Y CÁPSULAS
        $this->crearPresentacionesTabletas();
        
        // 2. JARABES Y LÍQUIDOS
        $this->crearPresentacionesJarabes();
        
        // 3. INYECTABLES
        $this->crearPresentacionesInyectables();
        
        // 4. CREMAS Y POMADAS
        $this->crearPresentacionesCremas();
        
        $this->command->info('Presentaciones de productos creadas exitosamente.');
    }
    
    /**
     * Presentaciones para tabletas y cápsulas
     */
    protected function crearPresentacionesTabletas(): void
    {
        // Buscar categorías de tabletas/cápsulas
        $categorias = Categoria::whereIn('nombre', [
            'Analgésicos',
            'Antibióticos',
            'Antiinflamatorios',
            'Vitaminas',
            'Antihistamínicos'
        ])->get();
        
        foreach ($categorias as $categoria) {
            $productos = Producto::where('categoria_id', $categoria->id)->get();
            
            foreach ($productos as $producto) {
                // Blíster x 10
                PresentacionProducto::create([
                    'producto_id' => $producto->id,
                    'nombre' => 'Blíster',
                    'descripcion' => 'Blíster x 10 unidades',
                    'unidades_por_presentacion' => 10,
                    'precio_sugerido' => $producto->precio_compra * 10 * 0.95, // 5% descuento
                    'activo' => true,
                    'orden' => 1,
                ]);
                
                // Caja x 20
                PresentacionProducto::create([
                    'producto_id' => $producto->id,
                    'nombre' => 'Caja',
                    'descripcion' => 'Caja x 20 unidades',
                    'unidades_por_presentacion' => 20,
                    'precio_sugerido' => $producto->precio_compra * 20 * 0.90, // 10% descuento
                    'activo' => true,
                    'orden' => 2,
                ]);
                
                // Caja x 100
                PresentacionProducto::create([
                    'producto_id' => $producto->id,
                    'nombre' => 'Caja Mayor',
                    'descripcion' => 'Caja x 100 unidades',
                    'unidades_por_presentacion' => 100,
                    'precio_sugerido' => $producto->precio_compra * 100 * 0.85, // 15% descuento
                    'activo' => true,
                    'orden' => 3,
                ]);
            }
        }
    }
    
    /**
     * Presentaciones para jarabes y líquidos
     */
    protected function crearPresentacionesJarabes(): void
    {
        // Buscar productos de jarabe
        $productos = Producto::where('nombre', 'like', '%jarabe%')
            ->orWhere('nombre', 'like', '%suspensión%')
            ->orWhere('descripcion', 'like', '%ml%')
            ->get();
        
        foreach ($productos as $producto) {
            // Frasco x 60ml
            PresentacionProducto::create([
                'producto_id' => $producto->id,
                'nombre' => 'Frasco 60ml',
                'descripcion' => 'Frasco de 60 ml',
                'unidades_por_presentacion' => 60,
                'precio_sugerido' => $producto->precio_compra * 60,
                'activo' => true,
                'orden' => 1,
            ]);
            
            // Frasco x 120ml
            PresentacionProducto::create([
                'producto_id' => $producto->id,
                'nombre' => 'Frasco 120ml',
                'descripcion' => 'Frasco de 120 ml',
                'unidades_por_presentacion' => 120,
                'precio_sugerido' => $producto->precio_compra * 120 * 0.95,
                'activo' => true,
                'orden' => 2,
            ]);
            
            // Caja x 12 frascos (120ml c/u)
            PresentacionProducto::create([
                'producto_id' => $producto->id,
                'nombre' => 'Caja x 12',
                'descripcion' => 'Caja x 12 frascos de 120ml',
                'unidades_por_presentacion' => 1440, // 12 * 120
                'precio_sugerido' => $producto->precio_compra * 1440 * 0.85,
                'activo' => true,
                'orden' => 3,
            ]);
        }
    }
    
    /**
     * Presentaciones para inyectables
     */
    protected function crearPresentacionesInyectables(): void
    {
        $productos = Producto::where('nombre', 'like', '%inyectable%')
            ->orWhere('nombre', 'like', '%ampolla%')
            ->orWhere('descripcion', 'like', '%inyección%')
            ->get();
        
        foreach ($productos as $producto) {
            // Caja x 5 ampollas
            PresentacionProducto::create([
                'producto_id' => $producto->id,
                'nombre' => 'Caja x 5',
                'descripcion' => 'Caja x 5 ampollas',
                'unidades_por_presentacion' => 5,
                'precio_sugerido' => $producto->precio_compra * 5 * 0.95,
                'activo' => true,
                'orden' => 1,
            ]);
            
            // Caja x 10 ampollas
            PresentacionProducto::create([
                'producto_id' => $producto->id,
                'nombre' => 'Caja x 10',
                'descripcion' => 'Caja x 10 ampollas',
                'unidades_por_presentacion' => 10,
                'precio_sugerido' => $producto->precio_compra * 10 * 0.90,
                'activo' => true,
                'orden' => 2,
            ]);
            
            // Caja x 100 ampollas
            PresentacionProducto::create([
                'producto_id' => $producto->id,
                'nombre' => 'Caja x 100',
                'descripcion' => 'Caja x 100 ampollas',
                'unidades_por_presentacion' => 100,
                'precio_sugerido' => $producto->precio_compra * 100 * 0.80,
                'activo' => true,
                'orden' => 3,
            ]);
        }
    }
    
    /**
     * Presentaciones para cremas y pomadas
     */
    protected function crearPresentacionesCremas(): void
    {
        $productos = Producto::where('nombre', 'like', '%crema%')
            ->orWhere('nombre', 'like', '%pomada%')
            ->orWhere('nombre', 'like', '%gel%')
            ->get();
        
        foreach ($productos as $producto) {
            // Tubo x 30g
            PresentacionProducto::create([
                'producto_id' => $producto->id,
                'nombre' => 'Tubo 30g',
                'descripcion' => 'Tubo de 30 gramos',
                'unidades_por_presentacion' => 30,
                'precio_sugerido' => $producto->precio_compra * 30,
                'activo' => true,
                'orden' => 1,
            ]);
            
            // Tubo x 60g
            PresentacionProducto::create([
                'producto_id' => $producto->id,
                'nombre' => 'Tubo 60g',
                'descripcion' => 'Tubo de 60 gramos',
                'unidades_por_presentacion' => 60,
                'precio_sugerido' => $producto->precio_compra * 60 * 0.95,
                'activo' => true,
                'orden' => 2,
            ]);
            
            // Caja x 12 tubos (60g c/u)
            PresentacionProducto::create([
                'producto_id' => $producto->id,
                'nombre' => 'Caja x 12',
                'descripcion' => 'Caja x 12 tubos de 60g',
                'unidades_por_presentacion' => 720, // 12 * 60
                'precio_sugerido' => $producto->precio_compra * 720 * 0.85,
                'activo' => true,
                'orden' => 3,
            ]);
        }
    }
}