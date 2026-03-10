<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::view('/docs', 'docs')
    ->name('docs')
    ->middleware(function ($request, $next) {
        $docsEnabled = filter_var(env('APP_DOCS_ENABLED', app()->isLocal() || app()->environment('testing')), FILTER_VALIDATE_BOOL);

        abort_unless($docsEnabled, 404);

        return $next($request);
    });

Route::get('/docs/openapi.yaml', function () {
    $docsEnabled = filter_var(env('APP_DOCS_ENABLED', app()->isLocal() || app()->environment('testing')), FILTER_VALIDATE_BOOL);

    abort_unless($docsEnabled, 404);

    return response()->file(
        base_path('openapi.yaml'),
        ['Content-Type' => 'application/yaml; charset=UTF-8']
    );
})->name('docs.openapi');
