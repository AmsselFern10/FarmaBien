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

        $provider = strtolower(config('services.ai.provider') ?: 'openai');
        $model = config('services.ai.model') ?: 'gpt-4o-mini';
        $timeout = (int)(config('services.ai.timeout') ?: 10);

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
  \"recomendaciones\": \"Recomendaciones del farmacéutico al paciente (alimentos, horarios, qué hacer ante olvido, hidratación, almacenamiento)\",
  \"advertencias\": \"Efectos secundarios frecuentes, signos de alerta y precauciones al conducir o en ancianos\",
  \"contraindicaciones\": \"Contraindicaciones absolutas, incompatibilidad en embarazo/lactancia e interacciones con otros fármacos\"
}";

            if ($provider === 'gemini') {
                $endpoint = config('services.ai.url') ?: "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
                $response = Http::timeout($timeout)->post($endpoint, [
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
                ])->timeout($timeout)->post($endpoint, [
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
            Log::warning('FarmaBien: Excepción al consultar API Externa de IA: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Motor de inferencia farmacológica local por principio activo y forma
     */
    protected function obtenerDatosFarmacologicos(string $principio, string $forma, string $conc): array
    {
        $p = mb_strtolower($principio);

        if (str_contains($p, 'paracetamol') || str_contains($p, 'acetaminofen')) {
            return [
                'uso_clinico' => "Analgésico y antipirético de primera línea. Indicado para alivio sintomático del dolor leve a moderado (cefaleas, dolor muscular, odontalgia) y estados febriles.",
                'posologia' => "Adultos: 500 mg a 1 g cada 6 u 8 horas según necesidad (dosis máxima: 4 g al día). Niños: 10 a 15 mg/kg por dosis cada 6 horas.",
                'recomendaciones' => "Puede tomarse con o sin alimentos. Mantener una hidratación adecuada. Evitar el consumo simultáneo de bebidas alcohólicas para no sobrecargar la función hepática.",
                'advertencias' => "No asociar con otros medicamentos que contengan paracetamol para evitar riesgo de hepatotoxicidad severa por sobredosis acumulada.",
                'contraindicaciones' => "Hipersensibilidad conocida al paracetamol. Insuficiencia hepática severa, hepatitis aguda o cirrosis descompensada."
            ];
        }

        if (str_contains($p, 'ibuprofeno')) {
            return [
                'uso_clinico' => "Antiinflamatorio no esteroideo (AINE) con potente acción analgésica, antipirética y antiinflamatoria en cuadros osteoarticulares, dolor dental, traumatismos y dismenorrea.",
                'posologia' => "Adultos: 400 mg a 600 mg cada 6 a 8 horas (máximo 2400 mg/día). Niños mayores de 3 meses: 5 a 10 mg/kg cada 8 horas.",
                'recomendaciones' => "Ingerir siempre acompañado de alimentos sólidos o un vaso de leche para proteger la mucosa gástrica y reducir la acidez.",
                'advertencias' => "Usar la dosis mínima eficaz durante el menor tiempo posible. Precaución en pacientes hipertensos o con patología cardiovascular.",
                'contraindicaciones' => "Úlcera gastroduodenal activa, sangrado digestivo previo, insuficiencia renal o cardíaca grave y tercer trimestre de gestación."
            ];
        }

        if (str_contains($p, 'amoxicilina')) {
            return [
                'uso_clinico' => "Antibiótico bactericida betalactámico de amplio espectro. Tratamiento de infecciones respiratorias altas y bajas, otitis media, infecciones dentales y urinarias.",
                'posologia' => "Adultos: 500 mg a 875 mg cada 8 a 12 horas por 7 a 10 días continuos. Niños: 40 a 90 mg/kg/día divididos en 2 o 3 tomas.",
                'recomendaciones' => "Tomar a horas exactas para mantener la concentración plasmática. Ingerir abundante agua. No interrumpir el ciclo aunque los síntomas desaparezcan antes.",
                'advertencias' => "Completar estrictamente el esquema para evitar la aparición de cepas bacterianas resistentes. Suspender si surge erupción cutánea pruriginosa.",
                'contraindicaciones' => "Antecedente de alergia o shock anafiláctico a penicilinas, cefalosporinas o betalactámicos. Mononucleosis infecciosa."
            ];
        }

        if (str_contains($p, 'omeprazol') || str_contains($p, 'pantoprazol') || str_contains($p, 'esomeprazol')) {
            return [
                'uso_clinico' => "Inhibidor selectivo de la bomba de protones (IBP). Reduce la secreción ácida gástrica en gastritis, úlcera péptica, reflujo gastroesofágico (ERGE) y dispepsia.",
                'posologia' => "Adultos: 20 mg a 40 mg una vez al día, administrado en ayunas por la mañana, 30 minutos antes del desayuno.",
                'recomendaciones' => "Tomar con un vaso de agua entera sin abrir, masticar ni triturar las cápsulas para mantener el recubrimiento gastrorresistente de los microgránulos.",
                'advertencias' => "El uso prolongado (mayor a 1 año) requiere monitorización médica de los niveles séricos de magnesio y absorción de vitamina B12.",
                'contraindicaciones' => "Hipersensibilidad a los derivados benzimidazólicos. No coadministrar con atazanavir o nelfinavir."
            ];
        }

        if (str_contains($p, 'loratadina') || str_contains($p, 'cetirizina')) {
            return [
                'uso_clinico' => "Antihistamínico H1 periférico de segunda generación. Alivio sintomático de rinitis alérgica estacional, urticaria idiopática crónica y prurito dérmico.",
                'posologia' => "Adultos y niños mayores de 12 años: 10 mg una vez al día (preferiblemente por la noche). Niños 2-12 años: 5 mg a 10 mg al día.",
                'recomendaciones' => "Tomar preferentemente en el mismo horario diario. Evitar la combinación con sedantes o alcohol durante el tratamiento.",
                'advertencias' => "Aunque tiene mínima penetración al sistema nervioso central, evaluar tolerancia individual antes de conducir o manejar maquinaria pesada.",
                'contraindicaciones' => "Hipersensibilidad a antihistamínicos H1. Evaluar riesgo-beneficio en pacientes con insuficiencia hepática o renal severa."
            ];
        }

        if (str_contains($p, 'naproxeno') || str_contains($p, 'ketorolaco') || str_contains($p, 'diclofenaco')) {
            return [
                'uso_clinico' => "Analgésico y antiinflamatorio para el tratamiento del dolor agudo musculoesquelético, postquirúrgico, traumatológico, gota y cólico renal.",
                'posologia' => "Adultos: 1 dosis cada 8 a 12 horas con comida. Para analgesia aguda no sobrepasar 5 días consecutivos de tratamiento.",
                'recomendaciones' => "Consumir con abundante agua y alimentos. En tratamientos de varios días, consultar al farmacéutico sobre la protección gástrica.",
                'advertencias' => "Puede incrementar la retención hidrosalina y descompensar la presión arterial en pacientes hipertensos.",
                'contraindicaciones' => "Gastritis erosiva activa, antecedentes de asma inducida por aspirina/AINEs, insuficiencia renal moderada a grave y hemorragia activa."
            ];
        }

        // Fallback clínico farmacéutico general
        return [
            'uso_clinico' => "Tratamiento farmacológico de acuerdo a la indicación terapéutica registrada en el prospecto del fabricante ({$forma} {$conc}).",
            'posologia' => "Administrar según la prescripción médica o las instrucciones del prospecto oficial, respetando intervalos y dosis horaria.",
            'recomendaciones' => "Conservar en su envase original, en ambiente fresco y seco protegido de la luz solar directa y fuera del alcance de los niños.",
            'advertencias' => "Si los síntomas no ceden tras 48 a 72 horas, o si experimenta reacciones adversas inesperadas, suspenda su administración y consulte al médico.",
            'contraindicaciones' => "Hipersensibilidad al principio activo o a los excipientes de la formulación. Consultar en caso de embarazo, lactancia o insuficiencia hepatorrenal."
        ];
    }
}

