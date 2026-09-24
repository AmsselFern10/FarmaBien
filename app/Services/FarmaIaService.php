<?php

namespace App\Services;

use App\Models\Producto;
use App\Models\Categoria;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FarmaIaService
{
    /**
     * Diccionario semántico de síntomas, dolencias y lenguaje natural
     * vinculado a principios activos y clases terapéuticas.
     */
    protected static array $mapaSintomas = [
        'dolor de cabeza' => ['paracetamol', 'ibuprofeno', 'ketorolaco', 'naproxeno', 'clonixinato', 'ergotamina', 'acetaminofen'],
        'cefalea'         => ['paracetamol', 'ibuprofeno', 'ketorolaco', 'naproxeno'],
        'migraña'         => ['ergotamina', 'naproxeno', 'ibuprofeno', 'ketorolaco', 'paracetamol'],
        'fiebre'          => ['paracetamol', 'ibuprofeno', 'metamizol', 'acetaminofen'],
        'calentura'       => ['paracetamol', 'metamizol', 'ibuprofeno'],
        'dolor muscular'  => ['ibuprofeno', 'naproxeno', 'diclofenaco', 'ketoprofeno', 'meloxicam', 'clonixinato'],
        'dolor de espalda'=> ['ibuprofeno', 'naproxeno', 'diclofenaco', 'ketoprofeno', 'meloxicam'],
        'golpe'           => ['ibuprofeno', 'diclofenaco', 'arnica', 'naproxeno'],
        'inflamacion'     => ['ibuprofeno', 'naproxeno', 'diclofenaco', 'dexametasona', 'prednisona'],
        'tos'             => ['dextrometorfano', 'ambroxol', 'bromhexina', 'clorfenamina', 'guaifenesina'],
        'tos seca'        => ['dextrometorfano', 'clorfenamina'],
        'tos con flema'   => ['ambroxol', 'bromhexina', 'guaifenesina'],
        'gripe'           => ['paracetamol', 'clorfenamina', 'pseudoefedrina', 'antigripal', 'ibuprofeno'],
        'resfrio'         => ['paracetamol', 'clorfenamina', 'pseudoefedrina', 'cetirizina'],
        'congestion'      => ['pseudoefedrina', 'clorfenamina', 'loratadina', 'oximetazolina'],
        'alergia'         => ['loratadina', 'cetirizina', 'clorfenamina', 'desloratadina', 'fexofenadina'],
        'picazon'         => ['loratadina', 'cetirizina', 'hidrocortisona', 'clorfenamina'],
        'rinitis'         => ['loratadina', 'cetirizina', 'fluticasona', 'desloratadina'],
        'acidez'          => ['omeprazol', 'pantoprazol', 'esomeprazol', 'hidroxido de aluminio', 'ranitidina'],
        'gastritis'       => ['omeprazol', 'esomeprazol', 'pantoprazol', 'sucralfato', 'hidroxido de aluminio'],
        'reflujo'         => ['omeprazol', 'esomeprazol', 'ranitidina', 'hidroxido de aluminio'],
        'ardor de estomago'=> ['omeprazol', 'hidroxido de aluminio', 'magnesio', 'bismuto'],
        'diarrea'         => ['loperamida', 'suero oral', 'sales de rehidratacion', 'probioticos', 'caolin'],
        'vomito'          => ['dimenhidrinato', 'metoclopramida', 'ondansetron'],
        'mareo'           => ['dimenhidrinato', 'meclizina'],
        'infeccion'       => ['amoxicilina', 'azitromicina', 'cefalexina', 'ciprofloxacino', 'ampicilina'],
        'garganta'        => ['amoxicilina', 'azitromicina', 'ibuprofeno', 'bencidamina', 'clorhexidina'],
        'amigdalitis'     => ['amoxicilina', 'azitromicina', 'ibuprofeno'],
        'dolor de muela'  => ['ketorolaco', 'ibuprofeno', 'amoxicilina', 'naproxeno', 'clonixinato'],
        'colico'          => ['hioscina', 'butilhioscina', 'propinoxato', 'ibuprofeno', 'clonixinato'],
        'dolor menstrual' => ['ibuprofeno', 'naproxeno', 'hioscina', 'clonixinato'],
        'presion'         => ['losartan', 'enalapril', 'amlodipino', 'captopril'],
        'diabetes'        => ['metformina', 'glibenclamida'],
        'estres'          => ['valeriana', 'pasiflora', 'complejo b'],
        'ansiedad'        => ['valeriana', 'complejo b'],
        'vitaminas'       => ['complejo b', 'vitamina c', 'multivitaminico', 'zinc', 'hierro'],
        'defensas'        => ['vitamina c', 'zinc', 'echinacea'],
    ];

    /**
     * Búsqueda semántica en lenguaje natural.
     * Retorna array de productos enriquecidos con explicación clínica.
     */
    public function buscarSemantica(string $query): array
    {
        $q = mb_strtolower(trim($query));
        if (strlen($q) < 2) {
            return [];
        }

        // 1. Identificar principios activos sugeridos por coincidencia semántica
        $principiosSugeridos = [];
        $motivoEncontrado = '';

        foreach (self::$mapaSintomas as $sintoma => $activos) {
            if (str_contains($q, $sintoma) || str_contains($sintoma, $q)) {
                $principiosSugeridos = array_unique(array_merge($principiosSugeridos, $activos));
                if (!$motivoEncontrado) {
                    $motivoEncontrado = ucfirst($sintoma);
                }
            }
        }

        // 2. Consultar base de datos
        $dbQuery = Producto::with(['categoria', 'laboratorio'])
            ->withSum(['lotes as stock_total' => function ($q) {
                $q->where('activo', true);
            }], 'stock_actual')
            ->where('activo', true);

        // Búsqueda combinada: coincidencia directa + principios semánticos
        $dbQuery->where(function ($sub) use ($q, $principiosSugeridos) {
            $sub->where('nombre', 'like', "%{$q}%")
                ->orWhere('principio_activo', 'like', "%{$q}%")
                ->orWhere('descripcion', 'like', "%{$q}%")
                ->orWhere('codigo_barra', 'like', "%{$q}%");

            if (!empty($principiosSugeridos)) {
                foreach ($principiosSugeridos as $activo) {
                    $sub->orWhere('principio_activo', 'like', "%{$activo}%")
                        ->orWhere('nombre', 'like', "%{$activo}%")
                        ->orWhere('descripcion', 'like', "%{$activo}%");
                }
            }

            // Coincidencia con categorías
            $sub->orWhereHas('categoria', function ($cat) use ($q) {
                $cat->where('nombre', 'like', "%{$q}%");
            });
        });

        $productos = $dbQuery->take(12)->get();

        // 3. Enriquecer cada producto con la explicación contextual
        return $productos->map(function ($p) use ($q, $motivoEncontrado, $principiosSugeridos) {
            $explicacion = $this->determinarExplicacion($p, $q, $motivoEncontrado, $principiosSugeridos);

            return [
                'id'               => $p->id,
                'nombre'           => $p->nombre,
                'principio_activo' => $p->principio_activo,
                'concentracion'    => $p->concentracion,
                'forma_farmaceutica'=> $p->forma_farmaceutica,
                'categoria'        => $p->categoria?->nombre ?? 'General',
                'laboratorio'      => $p->laboratorio?->nombre ?? 'N/A',
                'codigo_barra'     => $p->codigo_barra,
                'precio_venta'     => number_format((float)$p->precio_venta, 2),
                'stock_total'      => (int)($p->stock_total ?? 0),
                'stock_minimo'     => (int)($p->stock_minimo ?? 0),
                'activo'           => (bool)$p->activo,
                'requiere_receta'  => (bool)$p->requiere_receta,
                'imagen_url'       => $p->imagen ? asset('storage/' . $p->imagen) : null,
                'url_show'         => route('productos.show', $p->id),
                'explicacion'      => $explicacion,
            ];
        })->toArray();
    }

    /**
     * Generar explicación contextual para la sugerencia de IA
     */
    protected function determinarExplicacion(Producto $p, string $query, string $motivo, array $sugeridos): string
    {
        $pActivo = mb_strtolower($p->principio_activo ?? '');
        $pNombre = mb_strtolower($p->nombre);

        if ($motivo) {
            return "Indicado para alivio de {$motivo}. Contiene {$p->principio_activo} ({$p->concentracion}), que actúa directamente sobre el síntoma.";
        }

        foreach ($sugeridos as $sug) {
            if (str_contains($pActivo, $sug) || str_contains($pNombre, $sug)) {
                return "Coincide con el tratamiento de su consulta gracias a su acción farmacológica con {$p->principio_activo}.";
            }
        }

        if ($p->categoria) {
            return "Perteneciente al grupo de {$p->categoria->nombre} con acción terapéutica demostrada.";
        }

        return "Coincidencia relevante en el catálogo farmacéutico.";
    }

    /**
     * Generar ficha clínica detallada de un producto asistida por IA
     */
    public function generarFichaProducto(Producto $producto): array
    {
        $producto->loadMissing(['categoria', 'laboratorio']);

        $activo = $producto->principio_activo ?: $producto->nombre;
        $forma  = $producto->forma_farmaceutica ?: 'Tableta / Comprimido';
        $conc   = $producto->concentracion ?: '';
        $cat    = $producto->categoria?->nombre ?? 'General';

        // Buscar sustitutos genéricos en la base de datos (mismo principio activo o misma categoría)
        $sustitutos = Producto::where('id', '!=', $producto->id)
            ->where('activo', true)
            ->where(function ($q) use ($producto) {
                if ($producto->principio_activo) {
                    $q->where('principio_activo', 'like', "%{$producto->principio_activo}%");
                }
                if ($producto->categoria_id) {
                    $q->orWhere('categoria_id', $producto->categoria_id);
                }
            })
            ->take(4)
            ->get(['id', 'nombre', 'principio_activo', 'precio_venta', 'requiere_receta']);

        // 1. Intentar consultar API de IA Externa si está configurada en .env
        $aiExterna = $this->consultarIaExterna($producto->nombre, $activo, $forma, $conc, $cat);

        if ($aiExterna) {
            $datosClinicos = $aiExterna;
            $fuenteIa = 'api_externa';
            $generadoPor = 'FarmaBien AI Bridge (' . (config('services.ai.model') ?: 'API Externa') . ')';
        } else {
            // 2. Usar base de conocimiento farmacológico estructurado FarmaBien
            $datosClinicos = $this->obtenerDatosFarmacologicos($activo, $forma, $conc);
            $fuenteIa = 'motor_local';
            $generadoPor = 'Motor Farmacológico Clínico FarmaBien v2.0 (Listo para API Externa)';
        }

        return [
            'ok'                 => true,
            'fuente_ia'          => $fuenteIa,
            'api_configurada'    => !empty(config('services.ai.key')),
            'producto_id'        => $producto->id,
            'nombre'             => $producto->nombre,
            'principio_activo'   => $producto->principio_activo,
            'concentracion'      => $producto->concentracion,
            'forma_farmaceutica' => $forma,
            'categoria'          => $cat,
            'laboratorio'        => $producto->laboratorio?->nombre ?? 'N/A',
            'requiere_receta'    => (bool)$producto->requiere_receta,
            'tipo_control'       => $producto->tipo_control,
            'precio_venta'       => number_format((float)$producto->precio_venta, 2),
            'uso_clinico'        => $datosClinicos['uso_clinico'] ?? 'Alivio de síntomas y tratamiento según prescripción médica.',
            'posologia'          => $datosClinicos['posologia'],
            'recomendaciones'    => $datosClinicos['recomendaciones'] ?? 'Tomar con suficiente agua y seguir las indicaciones del facultativo.',
            'advertencias'       => $datosClinicos['advertencias'],
            'contraindicaciones' => $datosClinicos['contraindicaciones'],
            'sustitutos'         => $sustitutos->map(fn($s) => [
                'id'             => $s->id,
                'nombre'         => $s->nombre,
                'principio'      => $s->principio_activo,
                'precio'         => number_format((float)$s->precio_venta, 2),
                'requiere_receta'=> (bool)$s->requiere_receta,
                'url'            => route('productos.show', $s->id),
            ])->toArray(),
            'generado_por'       => $generadoPor,
        ];
    }

    /**
     * Conector a API Externa de IA (OpenAI, Gemini o endpoint compatible).
     * Retorna array con datos clínicos estructurados o null si no está disponible.
     */
    protected function consultarIaExterna(string $nombre, string $principio, string $forma, string $conc, string $cat): ?array
    {
        $apiKey = config('services.ai.key');
        if (empty($apiKey)) {
            return null;
        }

        $provider = strtolower(config('services.ai.provider') ?: 'gemini');
        $model = config('services.ai.model') ?: 'gemini-1.5-flash';
        $timeout = min(6, (int)(config('services.ai.timeout') ?: 4)); // Timeout corto para nunca congelar Render / Hosting

        try {
            $prompt = "Actúa como un médico farmacólogo y farmacéutico hospitalario. Genera la ficha técnica clínica para el medicamento:
Nombre comercial: {$nombre}
Principio activo: {$principio}
Concentración: {$conc}
Forma farmacéutica: {$forma}
Categoría: {$cat}

Debes responder ÚNICAMENTE un objeto JSON válido con estas 5 claves en español:
{
  \"uso_clinico\": \"Para qué sirve exactamente, indicaciones aprobadas y acción farmacológica\",
  \"posologia\": \"Dosis recomendada adultos/niños, frecuencia en horas y duración típica del tratamiento\",
  \"recomendaciones\": \"Recomendaciones del farmacéutico al paciente (alimentos, horarios, hidratación, almacenamiento)\",
  \"advertencias\": \"Efectos secundarios frecuentes, signos de alerta y precauciones al conducir o en ancianos\",
  \"contraindicaciones\": \"Contraindicaciones absolutas, incompatibilidad en embarazo/lactancia e interacciones con otros fármacos\"
}";

            if ($provider === 'gemini') {
                $endpoint = config('services.ai.url') ?: "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
                $response = Http::timeout($timeout)->connectTimeout(2)->post($endpoint, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'responseMimeType' => 'application/json'
                    ]
                ]);

                if ($response->successful()) {
                    $json = $response->json();
                    $rawText = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
                    $decoded = json_decode($rawText, true);
                    if ($decoded && isset($decoded['posologia'])) {
                        return $decoded;
                    }
                }
            } else {
                // Default: OpenAI or OpenAI-compatible endpoint (Groq, Together, DeepSeek, LocalAI, etc.)
                $endpoint = config('services.ai.url') ?: 'https://api.openai.com/v1/chat/completions';
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type'  => 'application/json',
                ])->timeout($timeout)->connectTimeout(2)->post($endpoint, [
                    'model' => $model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Eres un servicio de información farmacológica que devuelve estrictamente JSON con los campos: uso_clinico, posologia, recomendaciones, advertencias, contraindicaciones.'
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'response_format' => ['type' => 'json_object'],
                    'temperature' => 0.2
                ]);

                if ($response->successful()) {
                    $json = $response->json();
                    $content = $json['choices'][0]['message']['content'] ?? '';
                    $decoded = json_decode($content, true);
                    if ($decoded && isset($decoded['posologia'])) {
                        return $decoded;
                    }
                }
            }
        } catch (\Throwable $e) {
            // Falla silenciosa y segura: no congela la pantalla y activa el motor local inmediatamente
            Log::info('FarmaBien: API Externa no disponible o con latencia alta. Activando Motor Farmacológico Local.', [
                'error' => $e->getMessage()
            ]);
        }

        return null;
    }

    /**
     * Motor de inferencia farmacológica local por principio activo, categoría y forma
     * (100% Autónomo, offline y de respuesta instantánea <1ms).
     */
    protected function obtenerDatosFarmacologicos(string $principio, string $forma, string $conc): array
    {
        $p = mb_strtolower($principio);

        // Analgésicos y Antipiréticos
        if (str_contains($p, 'paracetamol') || str_contains($p, 'acetaminofen')) {
            return [
                'uso_clinico' => "Analgésico y antipirético de primera línea. Alivia el dolor leve a moderado (cefaleas, mialgias, odontalgias) y reduce la fiebre.",
                'posologia' => "Adultos: 500 mg a 1000 mg cada 6 a 8 horas (máximo 4 g/día). Niños: 10 a 15 mg/kg por toma cada 6 horas.",
                'recomendaciones' => "Puede tomarse con o sin alimentos. Mantener buena hidratación y evitar el consumo de bebidas alcohólicas durante el tratamiento.",
                'advertencias' => "No combinar con otros medicamentos que contengan paracetamol para prevenir hepatotoxicidad severa por sobredosis.",
                'contraindicaciones' => "Hipersensibilidad al paracetamol e insuficiencia hepática grave o hepatitis aguda."
            ];
        }

        if (str_contains($p, 'ibuprofeno')) {
            return [
                'uso_clinico' => "Antiinflamatorio no esteroideo (AINE), analgésico y antipirético para procesos inflamatorios osteomusculares, dolor dental y fiebre.",
                'posologia' => "Adultos: 400 mg a 600 mg cada 6 a 8 horas con alimentos (máximo 2400 mg/día). Niños: 5 a 10 mg/kg cada 8 horas.",
                'recomendaciones' => "Tomar siempre con alimentos sólidos o leche para reducir la irritación gástrica.",
                'advertencias' => "Utilizar la dosis mínima eficaz durante el menor tiempo posible. Precaución en pacientes hipertensos o cardiópatas.",
                'contraindicaciones' => "Úlcera péptica activa, hemorragia gastrointestinal, insuficiencia renal grave y tercer trimestre de embarazo."
            ];
        }

        if (str_contains($p, 'naproxeno') || str_contains($p, 'ketorolaco') || str_contains($p, 'diclofenaco') || str_contains($p, 'meloxicam') || str_contains($p, 'ketoprofeno')) {
            return [
                'uso_clinico' => "Potente antiinflamatorio y analgésico no esteroideo para dolor agudo musculoesquelético, postquirúrgico, traumatismos y artritis.",
                'posologia' => "Adultos: 1 dosis cada 8 a 12 horas según indicación médica y posología específica del preparado.",
                'recomendaciones' => "Administrar junto a las comidas principales con abundante agua. En tratamientos prolongados, considerar protector gástrico.",
                'advertencias' => "No superar el tiempo máximo de tratamiento recomendado (en ketorolaco no más de 5 días). Vigilar la presión arterial.",
                'contraindicaciones' => "Gastritis erosiva, úlcera gastroduodenal, insuficiencia renal o hepática avanzada y alergia a AINEs o aspirina."
            ];
        }

        if (str_contains($p, 'metamizol') || str_contains($p, 'dipirona')) {
            return [
                'uso_clinico' => "Analgésico y antipirético potente indicado en fiebre alta refractaria a otros tratamientos y dolores espasmódicos agudos.",
                'posologia' => "Adultos: 500 mg a 1000 mg cada 6 a 8 horas (máximo 4 g/día).",
                'recomendaciones' => "Tomar con abundante agua. Respetar la prescripción médica sin sobrepasar los días indicados.",
                'advertencias' => "Suspender inmediatamente si aparece fiebre inexplicable o úlceras bucales (riesgo de agranulocitosis).",
                'contraindicaciones' => "Alergia a pirazolonas, porfiria hepática aguda, asma inducida por analgésicos y primer/tercer trimestre de embarazo."
            ];
        }

        // Antibacterianos y Antimicrobianos
        if (str_contains($p, 'amoxicilina') || str_contains($p, 'ampicilina') || str_contains($p, 'clavulan')) {
            return [
                'uso_clinico' => "Antibiótico bactericida betalactámico para infecciones respiratorias, otorrinolaringológicas, odontológicas y urinarias.",
                'posologia' => "Adultos: 500 mg a 875 mg cada 8 a 12 horas durante 7 a 10 días continuos. Niños: 40 a 90 mg/kg/día divididos en 2 o 3 tomas.",
                'recomendaciones' => "Cumplir estrictamente el horario prescrito. Tomar con agua y no suspender el tratamiento antes del tiempo indicado.",
                'advertencias' => "El abandono prematuro genera resistencia bacteriana. Suspender si se presentan erupciones dérmicas o prurito.",
                'contraindicaciones' => "Alergia o antecedentes de anafilaxia a penicilinas, ampicilinas o cefalosporinas."
            ];
        }

        if (str_contains($p, 'azitromicina') || str_contains($p, 'claritromicina') || str_contains($p, 'eritromicina')) {
            return [
                'uso_clinico' => "Antibiótico macrólido para infecciones del tracto respiratorio superior e inferior, piel, tejidos blandos y faringoamigdalitis.",
                'posologia' => "Adultos: 500 mg una vez al día por 3 a 5 días continuos según indicación médica.",
                'recomendaciones' => "Tomar 1 hora antes de las comidas o 2 horas después de las mismas para óptima absorción.",
                'advertencias' => "Precaución en pacientes con arritmias cardíacas o prolongación del intervalo QT.",
                'contraindicaciones' => "Hipersensibilidad a macrólidos o cetólidos e insuficiencia hepática grave."
            ];
        }

        if (str_contains($p, 'ciprofloxacino') || str_contains($p, 'levofloxacino') || str_contains($p, 'norfloxacino')) {
            return [
                'uso_clinico' => "Antibiótico fluoroquinolona de amplio espectro para infecciones urinarias complicadas, gastrointestinales y respiratorias.",
                'posologia' => "Adultos: 250 mg a 500 mg cada 12 horas por 5 a 10 días según criterio médico.",
                'recomendaciones' => "Beber abundante líquido durante el día. Evitar tomar simultáneamente con antiácidos, calcio, hierro o lácteos.",
                'advertencias' => "Evitar la exposición solar prolongada (fotosensibilidad). Suspender ante dolor o inflamación en tendones.",
                'contraindicaciones' => "Hipersensibilidad a quinolonas, antecedentes de tendinitis/rotura tendinosa, menores de 18 años y embarazo."
            ];
        }

        // Gastrointestinal y Protectores Gástricos
        if (str_contains($p, 'omeprazol') || str_contains($p, 'pantoprazol') || str_contains($p, 'esomeprazol') || str_contains($p, 'lansoprazol')) {
            return [
                'uso_clinico' => "Inhibidor de la bomba de protones (IBP) que reduce la acidez estomacal en reflujo gastroesofágico, gastritis y úlceras.",
                'posologia' => "Adultos: 20 mg a 40 mg una vez al día, preferiblemente en ayunas por la mañana, 30 minutos antes del desayuno.",
                'recomendaciones' => "Ingerir las cápsulas enteras con agua sin masticar, triturar ni disolver para conservar la capa gastrorresistente.",
                'advertencias' => "El uso continuo por más de un año requiere control de niveles de magnesio y vitamina B12.",
                'contraindicaciones' => "Hipersensibilidad a benzimidazoles sustituidos. No administrar conjuntamente con atazanavir o nelfinavir."
            ];
        }

        if (str_contains($p, 'hidroxido') || str_contains($p, 'magaldrato') || str_contains($p, 'bismuto') || str_contains($p, 'simeticona') || str_contains($p, 'antiacido')) {
            return [
                'uso_clinico' => "Antiácido y antiflatulento de acción local para el alivio rápido del ardor estomacal, acidez, pirosis y distensión gaseosa.",
                'posologia' => "Adultos: 1 a 2 cucharadas o tabletas masticables 1 hora después de las comidas principales y al acostarse.",
                'recomendaciones' => "Masticar bien los comprimidos o agitar la suspensión oral antes de tomar. Separar 2 horas de otros fármacos.",
                'advertencias' => "No emplear de forma continua durante más de 14 días sin evaluación médica.",
                'contraindicaciones' => "Insuficiencia renal severa, hipofosfatemia y sospecha de obstrucción intestinal."
            ];
        }

        // Antialérgicos y Antihistamínicos
        if (str_contains($p, 'loratadina') || str_contains($p, 'cetirizina') || str_contains($p, 'desloratadina') || str_contains($p, 'fexofenadina') || str_contains($p, 'clorfenamina')) {
            return [
                'uso_clinico' => "Antihistamínico para el alivio sintomático de rinitis alérgica, estornudos, secreción nasal, conjuntivitis alérgica y urticaria.",
                'posologia' => "Adultos y niños >12 años: 10 mg una vez al día (o clorfenamina 4 mg cada 6 a 8 h). Niños 2-12 años: 5 mg al día.",
                'recomendaciones' => "Tomar preferentemente en la noche con agua. Evitar el consumo de bebidas alcohólicas durante el tratamiento.",
                'advertencias' => "En antihistamínicos de 1ª generación (clorfenamina) puede presentarse somnolencia. Precaución al conducir.",
                'contraindicaciones' => "Hipersensibilidad al principio activo, embarazo (evaluar riesgo/beneficio) y glaucoma de ángulo cerrado."
            ];
        }

        // Respiratorio: Tos, Expectorantes y Antigripales
        if (str_contains($p, 'dextrometorfano') || str_contains($p, 'ambroxol') || str_contains($p, 'bromhexina') || str_contains($p, 'acetilcisteina') || str_contains($p, 'guaifenesina')) {
            return [
                'uso_clinico' => "Mucolítico, expectorante o antitusivo para el tratamiento de afecciones respiratorias con secreción mucosa o tos irritativa.",
                'posologia' => "Adultos: 1 dosis (10 a 15 ml de jarabe o 1 comprimido) cada 8 horas según la concentración del producto.",
                'recomendaciones' => "Aumentar la ingesta diaria de agua (mínimo 2 litros) para fluidificar el moco y facilitar su expulsión.",
                'advertencias' => "No suprimir la tos productiva con antitusivos de acción central sin indicación médica.",
                'contraindicaciones' => "Crisis asmática aguda, úlcera gastroduodenal activa y niños menores de 2 años salvo prescripción pediátrica."
            ];
        }

        // Antiespasmódicos y Digestivos
        if (str_contains($p, 'hioscina') || str_contains($p, 'butilhioscina') || str_contains($p, 'propinoxato') || str_contains($p, 'trimebutina')) {
            return [
                'uso_clinico' => "Antiespasmódico visceral para el alivio de cólicos intestinales, biliares, renales y dismenorrea espasmódica.",
                'posologia' => "Adultos: 10 mg a 20 mg cada 6 a 8 horas según la intensidad del espasmo (máximo 6 tomas diarias).",
                'recomendaciones' => "Tomar con medio vaso de agua al momento de presentarse el dolor espasmódico.",
                'advertencias' => "Puede ocasionar sequedad bucal y ligera visión borrosa temporal.",
                'contraindicaciones' => "Glaucoma de ángulo cerrado, hipertrofia prostática con retención urinaria y estenosis pilórica."
            ];
        }

        // Antihipertensivos y Cardiovasculares
        if (str_contains($p, 'losartan') || str_contains($p, 'enalapril') || str_contains($p, 'captopril') || str_contains($p, 'amlodipino') || str_contains($p, 'hidroclorotiazida')) {
            return [
                'uso_clinico' => "Antihipertensivo para el control y mantenimiento de la presión arterial sistémica y protección cardiovascular/renal.",
                'posologia' => "Adultos: Dosis diaria fija establecida por el médico cardiólogo/internista, generalmente 1 toma cada 24 horas por la mañana.",
                'recomendaciones' => "Tomar diariamente a la misma hora. No suspender bruscamente el tratamiento aunque la presión esté normalizada.",
                'advertencias' => "Medir periódicamente la presión arterial. Evitar levantarse bruscamente de la cama para prevenir mareos por hipotensión ortostática.",
                'contraindicaciones' => "Hipersensibilidad al principio activo, estenosis bilateral de arteria renal y segundo/tercer trimestre de embarazo."
            ];
        }

        // Antidiabéticos y Metabólicos
        if (str_contains($p, 'metformina') || str_contains($p, 'glibenclamida') || str_contains($p, 'empagliflozina') || str_contains($p, 'insulina')) {
            return [
                'uso_clinico' => "Hipoglucemiante oral para el tratamiento y control metabólico de la diabetes mellitus tipo 2.",
                'posologia' => "Adultos: 500 mg a 850 mg junto o después de las comidas principales según el esquema indicado por el endocrinólogo.",
                'recomendaciones' => "Acompañar siempre de una dieta balanceada y actividad física regular. Llevar un registro de glucemias capilares.",
                'advertencias' => "Reconocer síntomas de hipoglucemia (sudoración fría, temblor, mareos). Suspender antes de estudios radiológicos con contraste.",
                'contraindicaciones' => "Insuficiencia renal moderada a grave, cetoacidosis diabética, insuficiencia cardíaca descompensada y alcoholismo."
            ];
        }

        // Corticosteroides
        if (str_contains($p, 'dexametasona') || str_contains($p, 'prednisona') || str_contains($p, 'betametasona') || str_contains($p, 'hidrocortisona')) {
            return [
                'uso_clinico' => "Glucocorticoide con intensa acción antiinflamatoria e inmunosupresora en cuadros alérgicos severos, asma y procesos autoinmunes.",
                'posologia' => "Adultos: Dosis individualizada bajo estricto control médico, preferiblemente en toma única matutina.",
                'recomendaciones' => "Ingerir con alimentos para disminuir la irritación gástrica. No suspender abruptamente tratamientos de más de 7 días.",
                'advertencias' => "El uso prolongado puede elevar la glucosa y la presión arterial. Evitar la automedicación.",
                'contraindicaciones' => "Infecciones fúngicas o bacterianas sistémicas no tratadas y vacunación con virus vivos atenuados."
            ];
        }

        // Vitaminas, Minerales y Suplementos
        if (str_contains($p, 'complejo b') || str_contains($p, 'vitamina') || str_contains($p, 'zinc') || str_contains($p, 'calcio') || str_contains($p, 'hierro') || str_contains($p, 'acido folico')) {
            return [
                'uso_clinico' => "Suplemento nutricional y terapéutico para estados carenciales, refuerzo inmunológico, convalecencia, anemia y salud neurológica.",
                'posologia' => "Adultos: 1 tableta o cápsula al día con el desayuno o el almuerzo.",
                'recomendaciones' => "Tomar con un vaso de agua o jugo cítrico (en el caso del hierro para maximizar su absorción).",
                'advertencias' => "Los suplementos no sustituyen una alimentación balanceada. No superar las dosis diarias recomendadas.",
                'contraindicaciones' => "Hipersensibilidad a los componentes de la fórmula o hipervitaminosis preexistente."
            ];
        }

        // Antidiarreicos, Antiparasitarios y Rehidratación
        if (str_contains($p, 'loperamida') || str_contains($p, 'suero') || str_contains($p, 'probiotico') || str_contains($p, 'albendazol') || str_contains($p, 'metronidazol')) {
            return [
                'uso_clinico' => "Tratamiento sintomático de procesos diarreicos agudos, restauración de la microbiota intestinal o erradicación parasitaria.",
                'posologia' => "Según el producto: Suero oral a libre demanda; Loperamida 4 mg inicial y 2 mg tras cada deposición líquida (máx. 16 mg/día).",
                'recomendaciones' => "Mantener hidratación constante con sales de rehidratación oral. Evitar comidas grasas o lácteos durante el cuadro.",
                'advertencias' => "No usar loperamida si las heces presentan sangre (disentería) o fiebre alta.",
                'contraindicaciones' => "Colitis pseudomembranosa asociada a antibióticos y diarrea invasiva bacteriana."
            ];
        }

        // Fallback clínico farmacéutico general estructurado
        return [
            'uso_clinico' => "Medicamento formulado para el tratamiento de su indicación terapéutica ({$forma} {$conc}) de acuerdo al prospecto oficial.",
            'posologia' => "Administrar según la prescripción médica o las instrucciones del envase original, respetando intervalos horarios.",
            'recomendaciones' => "Conservar en lugar fresco y seco (<30°C), protegido de la luz y fuera del alcance de los niños. Tomar con agua.",
            'advertencias' => "Si los síntomas persisten tras 48 a 72 horas o nota efectos adversos inesperados, suspenda el uso y consulte a su médico.",
            'contraindicaciones' => "Hipersensibilidad al principio activo o a sus excipientes. Consultar al farmacéutico en embarazo o lactancia."
        ];
    }
}

