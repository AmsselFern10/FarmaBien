<?php

namespace App\Services\Ai;

use App\Models\Producto;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ProductoSmartSearchService
{
    public function __construct(private GroqService $groq) {}

    /**
     * Retorna resultados ordenados por relevancia (sin modificar datos).
     */
    public function buscar(string $consulta, bool $incluirInactivos = false, int $limit = 12): array
    {
        $consulta = trim($consulta);
        if (mb_strlen($consulta) < 2) return [];

        $plan = $this->planDeBusqueda($consulta);

        $keywords = array_values(array_unique(array_filter(array_map(function ($k) {
            $k = trim((string) $k);
            if ($k === '') return null;
            if (mb_strlen($k) > 40) $k = mb_substr($k, 0, 40);
            return $k;
        }, $plan['keywords'] ?? []))));

        // fallback: keywords por tokenización
        if (count($keywords) === 0) {
            $keywords = $this->fallbackKeywords($consulta);
        }

        $q = Producto::with('categoria')
            ->when(!$incluirInactivos, fn($qq) => $qq->where('activo', true))
            ->where(function ($w) use ($consulta, $keywords, $plan) {
                // original
                $w->where('nombre', 'like', '%' . $consulta . '%')
                  ->orWhere('codigo_barra', 'like', '%' . $consulta . '%');

                // keywords
                foreach ($keywords as $kw) {
                    $w->orWhere('nombre', 'like', '%' . $kw . '%')
                      ->orWhere('codigo_barra', 'like', '%' . $kw . '%');
                }

                // categorías sugeridas
                $cats = $plan['categorias'] ?? [];
                foreach ($cats as $c) {
                    $c = trim((string) $c);
                    if ($c === '') continue;
                    $w->orWhereHas('categoria', fn($cq) => $cq->where('nombre', 'like', '%' . $c . '%'));
                }
            })
            ->limit(60)
            ->get();

        $items = $q->map(function (Producto $p) use ($consulta, $keywords, $plan) {
            $nombre = (string) $p->nombre;
            $categoria = (string) optional($p->categoria)->nombre;

            $score = 0;

            if (Str::contains(Str::lower($nombre), Str::lower($consulta))) $score += 12;
            if ($p->codigo_barra && Str::contains((string) $p->codigo_barra, preg_replace('/\s+/', '', $consulta))) $score += 6;

            foreach ($keywords as $kw) {
                $kwL = Str::lower($kw);
                if ($kwL && Str::contains(Str::lower($nombre), $kwL)) $score += 4;
                if ($kwL && Str::contains(Str::lower($categoria), $kwL)) $score += 2;
            }

            // sesgos operativos: stock disponible y activo
            $stockTotal = (int) ($p->stock_total ?? 0);
            if ($stockTotal > 0) $score += 2;
            if ((bool) $p->activo) $score += 1;

            // explicación corta (no clínica) para UI
            $exp = $this->explicacionCorta($consulta, $keywords, $plan, $nombre, $categoria);

            return [
                'id' => $p->id,
                'nombre' => $nombre,
                'categoria' => $categoria ?: null,
                'codigo_barra' => $p->codigo_barra,
                'precio_venta' => number_format((float) ($p->precio_venta ?? 0), 2, '.', ''),
                'stock_total' => $stockTotal,
                'stock_minimo' => (int) ($p->stock_minimo ?? 0),
                'activo' => (bool) $p->activo,
                'requiere_receta' => (bool) $p->requiere_receta,
                'url_show' => route('productos.show', $p),
                'score' => $score,
                'explicacion' => $exp,
            ];
        })->sortByDesc('score')->values()->take($limit)->all();

        return $items;
    }

    private function planDeBusqueda(string $consulta): array
    {
        $cacheKey = 'ai:producto:plan_busqueda:' . md5(mb_strtolower($consulta));

        return Cache::remember($cacheKey, now()->addDays(2), function () use ($consulta) {
            $system = <<<SYS
Eres un asistente de búsqueda para un ERP de farmacia.
Tu tarea es convertir la consulta del usuario en un plan de búsqueda para un catálogo interno.

Devuelve SOLO JSON válido con estas claves exactas:
- keywords: array de strings (máx 12)
- categorias: array de strings (máx 6)
- requiere_receta: true | false | null

Reglas:
- Keywords cortas (1-3 palabras), en español.
- Si el usuario describe síntomas/uso (ej: "dolor y fiebre"), sugiere términos genéricos/ingredientes comunes (ej: "paracetamol", "ibuprofeno", "analgésico", "antitérmico").
- NO inventes marcas.
- NO des recomendaciones clínicas.
SYS;

            $user = "Consulta del usuario: {$consulta}";

            $raw = $this->groq->chat([
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $user],
            ], ['temperature' => 0.2, 'max_completion_tokens' => 220]);

            $json = $this->extractJson($raw);

            $plan = is_array($json) ? $json : [];
            if (!isset($plan['keywords']) || !is_array($plan['keywords'])) $plan['keywords'] = [];
            if (!isset($plan['categorias']) || !is_array($plan['categorias'])) $plan['categorias'] = [];
            if (!array_key_exists('requiere_receta', $plan)) $plan['requiere_receta'] = null;

            return $plan;
        });
    }

    private function extractJson(string $raw): ?array
    {
        $raw = trim($raw);
        if ($raw === '') return null;

        // si viene puro JSON
        $decoded = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) return $decoded;

        // intenta extraer el primer bloque {...}
        if (preg_match('/\{[\s\S]*\}/', $raw, $m)) {
            $decoded2 = json_decode($m[0], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded2)) return $decoded2;
        }

        return null;
    }

    private function fallbackKeywords(string $consulta): array
    {
        $tokens = preg_split('/\s+/', mb_strtolower($consulta));
        $stop = ['de','la','el','los','las','para','por','y','o','un','una','con','sin','en','del','al'];
        $kw = [];
        foreach ($tokens as $t) {
            $t = trim($t);
            if ($t === '' || in_array($t, $stop, true)) continue;
            if (mb_strlen($t) < 3) continue;
            $kw[] = $t;
        }
        return array_slice(array_values(array_unique($kw)), 0, 8);
    }

    private function explicacionCorta(string $consulta, array $keywords, array $plan, string $nombre, string $categoria): ?string
    {
        // Explicación operativa (no clínica): por qué aparece en resultados.
        $hits = [];

        if (Str::contains(Str::lower($nombre), Str::lower($consulta))) $hits[] = 'coincide con tu texto';
        foreach ($keywords as $kw) {
            if ($kw && Str::contains(Str::lower($nombre), Str::lower($kw))) {
                $hits[] = "coincide con “{$kw}”";
                break;
            }
        }
        foreach (($plan['categorias'] ?? []) as $c) {
            if ($c && Str::contains(Str::lower($categoria), Str::lower($c))) {
                $hits[] = "categoría “{$c}”";
                break;
            }
        }

        if (!$hits) return null;
        return 'Encontrado porque ' . implode(', ', $hits) . '.';
    }
}
