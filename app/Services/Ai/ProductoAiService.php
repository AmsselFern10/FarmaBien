<?php

namespace App\Services\Ai;

use App\Models\Producto;
use Illuminate\Support\Facades\Cache;

class ProductoAiService
{
    public function __construct(private GroqService $groq) {}

    public function ficha(Producto $producto): string
    {
        $cacheKey = 'ai:producto:ficha:' . $producto->id . ':' . optional($producto->updated_at)->timestamp;

        return Cache::remember($cacheKey, now()->addHours(12), function () use ($producto) {
            $nombre = (string) $producto->nombre;
            $categoria = (string) optional($producto->categoria)->nombre;
            $descripcion = (string) ($producto->descripcion ?? '');
            $requiereReceta = (bool) ($producto->requiere_receta ?? false);

            $system = <<<SYS
Eres un asistente de farmacia para un ERP. Responde en español neutro.

Reglas estrictas:
- NO inventes datos clínicos. Si no tienes certeza, escribe: "No disponible con la información del sistema".
- No des diagnóstico ni tratamiento personalizado.
- En "Dosis": si el sistema no trae principio activo/concentración/prospecto, indica que debe verificarse el prospecto o prescripción (no pongas números).
- Siempre incluye una nota de seguridad: "Información educativa, no sustituye a un profesional".

Formato obligatorio con secciones:
1) ¿Qué es? Explica brevemente el producto, su principio activo  y su mecanismo de acción (si se conoce). Si no se tiene información, indica que es un producto con datos limitados.
2) ¿Para qué sirve?
3) Dosis (general/según prospecto)
4) Precauciones y contraindicaciones
5) Efectos secundarios (consecuencias)
6) Interacciones comunes
7) Si requiere receta: (Sí/No y qué significa)
SYS;

            $user = <<<USR
Producto: {$nombre}
Categoría: {$categoria}
Descripción interna: {$descripcion}
Requiere receta: {$requiereReceta}

Genera la ficha solicitada.
USR;

            $content = $this->groq->chat([
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $user],
            ]);

            return trim($content) ?: 'No se pudo generar respuesta.';
        });
    }
}
