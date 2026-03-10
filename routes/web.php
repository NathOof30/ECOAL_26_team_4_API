<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/health/live', function () {
    return response()->json([
        'status' => 'ok',
        'app' => config('app.name'),
        'version' => env('APP_VERSION', 'dev'),
        'environment' => app()->environment(),
        'timestamp' => now()->toIso8601String(),
    ]);
})->name('health.live');

Route::get('/health/ready', function () {
    $checks = [
        'database' => [
            'status' => 'unknown',
        ],
    ];

    try {
        DB::connection()->getPdo();
        $checks['database']['status'] = 'ok';
    } catch (\Throwable $exception) {
        $checks['database'] = [
            'status' => 'error',
            'message' => 'Database connection failed.',
        ];

        Log::channel('operations')->error('health.ready_failed', [
            'check' => 'database',
            'error' => $exception->getMessage(),
        ]);

        return response()->json([
            'status' => 'error',
            'app' => config('app.name'),
            'version' => env('APP_VERSION', 'dev'),
            'environment' => app()->environment(),
            'timestamp' => now()->toIso8601String(),
            'checks' => $checks,
        ], 503);
    }

    return response()->json([
        'status' => 'ok',
        'app' => config('app.name'),
        'version' => env('APP_VERSION', 'dev'),
        'environment' => app()->environment(),
        'timestamp' => now()->toIso8601String(),
        'checks' => $checks,
    ]);
})->name('health.ready');

Route::get('/docs', function () {
    $docsEnabled = filter_var(env('APP_DOCS_ENABLED', app()->isLocal() || app()->environment('testing')), FILTER_VALIDATE_BOOL);

    abort_unless($docsEnabled, 404);

    return view('docs');
})->name('docs');

Route::get('/docs/openapi.yaml', function () {
    $docsEnabled = filter_var(env('APP_DOCS_ENABLED', app()->isLocal() || app()->environment('testing')), FILTER_VALIDATE_BOOL);

    abort_unless($docsEnabled, 404);

    return response()->file(
        base_path('openapi.yaml'),
        ['Content-Type' => 'application/yaml; charset=UTF-8']
    );
})->name('docs.openapi');
