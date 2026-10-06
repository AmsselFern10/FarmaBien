<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Health Check API Endpoint (Para monitorización, Kubernetes, Docker, Render, Cloudflare)
// En producción solo devuelve status y timestamp — no expone stack tecnológico
Route::get('/health', function () {
    $dbConnected = false;
    try {
        DB::connection()->getPdo();
        $dbConnected = true;
    } catch (\Throwable $e) {}

    $status = $dbConnected ? 200 : 503;

    $payload = [
        'status'    => $dbConnected ? 'healthy' : 'unhealthy',
        'timestamp' => now()->toIsoString(),
    ];

    if (!app()->environment('production')) {
        $payload['app']      = config('app.name', 'FarmaBien');
        $payload['version']  = '2.0.0';
        $payload['env']      = config('app.env');
        $payload['database'] = ['driver' => config('database.default'), 'connected' => $dbConnected];
        $payload['cache']    = config('cache.default');
    }

    return response()->json($payload, $status);
})->name('api.health');
