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
Route::get('/health', function () {
    $dbConnected = false;
    $dbDriver = config('database.default');
    try {
        DB::connection()->getPdo();
        $dbConnected = true;
    } catch (\Throwable $e) {}

    $status = $dbConnected ? 200 : 503;
    return response()->json([
        'status'      => $dbConnected ? 'healthy' : 'unhealthy',
        'app'         => config('app.name', 'FarmaBien'),
        'version'     => '2.0.0',
        'environment' => config('app.env'),
        'database'    => [
            'driver'    => $dbDriver,
            'connected' => $dbConnected,
        ],
        'cache'       => config('cache.default'),
        'timestamp'   => now()->toIso8601String(),
    ], $status);
})->name('api.health');
