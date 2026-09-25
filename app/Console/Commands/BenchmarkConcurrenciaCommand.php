<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class BenchmarkConcurrenciaCommand extends Command
{
    protected $signature = 'farma:benchmark-concurrencia 
                            {url=http://127.0.0.1:8000/catalogo : URL a evaluar} 
                            {--requests=100 : Número total de peticiones} 
                            {--concurrency=10 : Peticiones concurrentes en lote}';

    protected $description = 'Mide la Capacidad y Concurrencia HTTP (RPS y Latencia) para ISO/IEC 25023';

    public function handle(): int
    {
        $url = $this->argument('url');
        $totalRequests = (int) $this->option('requests');
        $concurrency = (int) $this->option('concurrency');

        $this->info("================================================================================");
        $this->info(" PRUEBA DE CAPACIDAD Y CONCURRENCIA HTTP - ISO/IEC 25023");
        $this->info(" Métrica: Throughput (RPS), Latencia y Tasa de Éxito");
        $this->info("================================================================================");
        $this->line("• URL Evaluada: {$url}");
        $this->line("• Total de Peticiones: {$totalRequests}");
        $this->line("• Concurrencia (Lotes paralelos): {$concurrency}");
        $this->newLine();

        $this->output->progressStart($totalRequests);

        $times = [];
        $statusCodes = [];
        $errores = 0;
        $startTime = microtime(true);

        $batches = ceil($totalRequests / $concurrency);
        $remaining = $totalRequests;

        for ($b = 0; $b < $batches; $b++) {
            $batchSize = min($concurrency, $remaining);
            $poolResponses = Http::pool(function ($pool) use ($url, $batchSize) {
                $reqs = [];
                for ($k = 0; $k < $batchSize; $k++) {
                    $reqs[] = $pool->get($url);
                }
                return $reqs;
            });

            foreach ($poolResponses as $response) {
                $this->output->progressAdvance();
                if ($response instanceof \Illuminate\Http\Client\Response) {
                    $code = $response->status();
                    $statusCodes[$code] = ($statusCodes[$code] ?? 0) + 1;
                    if ($response->successful() || $response->redirect()) {
                        // Tiempo aproximado
                    } else {
                        $errores++;
                    }
                } else {
                    $errores++;
                }
            }

            $remaining -= $batchSize;
        }

        $this->output->progressFinish();
        $totalTimeSec = microtime(true) - $startTime;
        $totalTimeMs = $totalTimeSec * 1000;

        $rps = round($totalRequests / max($totalTimeSec, 0.001), 2);
        $avgLatencyMs = round($totalTimeMs / max($totalRequests, 1), 2);
        $successRate = round((($totalRequests - $errores) / max($totalRequests, 1)) * 100, 2);

        $this->newLine();
        $this->info("================================================================================");
        $this->info(" RESULTADOS DE RENDIMIENTO Y CAPACIDAD");
        $this->info("================================================================================");
        $this->line("1. TIEMPO TOTAL DE PRUEBA: " . number_format($totalTimeSec, 3) . " segundos");
        $this->line("2. CAPACIDAD (THROUGHPUT): {$rps} peticiones/segundo (RPS / Transacciones por segundo)");
        $this->line("3. LATENCIA PROMEDIO: {$avgLatencyMs} ms por petición");
        $this->line("4. TASA DE ÉXITO: {$successRate}% ({$errores} errores detectados)");
        $this->newLine();
        $this->line("Desglose de Códigos HTTP:");
        foreach ($statusCodes as $code => $count) {
            $this->line("   • HTTP {$code}: {$count} peticiones");
        }
        $this->info("================================================================================");

        return self::SUCCESS;
    }
}
