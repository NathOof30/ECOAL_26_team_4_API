<?php

use App\Http\Middleware\EnsureUserType;
use App\Support\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'user_type' => EnsureUserType::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (AuthenticationException $exception, $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error('Unauthenticated.', 401);
            }
        });

        $exceptions->render(function (AuthorizationException $exception, $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error($exception->getMessage() ?: 'Forbidden.', 403);
            }
        });

        $exceptions->render(function (ModelNotFoundException $exception, $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error('Resource not found.', 404);
            }
        });

        $exceptions->render(function (ValidationException $exception, $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error('The given data was invalid.', 422, $exception->errors());
            }
        });

        $exceptions->render(function (ThrottleRequestsException $exception, $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error('Too many requests.', 429);
            }
        });

        $exceptions->render(function (HttpExceptionInterface $exception, $request) {
            if ($request->is('api/*')) {
                $status = $exception->getStatusCode();
                $message = $exception->getMessage() ?: match ($status) {
                    401 => 'Unauthenticated.',
                    403 => 'Forbidden.',
                    404 => 'Resource not found.',
                    409 => 'Conflict.',
                    default => 'Request failed.',
                };

                return ApiResponse::error($message, $status);
            }
        });
    })->create();
