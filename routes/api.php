<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\UsersController;
use App\Http\Controllers\API\CollectionsController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\ItemsController;
use App\Http\Controllers\API\CriteriaController;
use App\Http\Controllers\API\ItemCriteriaController;
use App\Http\Controllers\API\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');

// Public read routes
Route::apiResource('users', UsersController::class)->only(['index', 'show']);
Route::apiResource('collections', CollectionsController::class)->only(['index', 'show']);
Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);
Route::apiResource('items', ItemsController::class)->only(['index', 'show']);
Route::apiResource('criteria', CriteriaController::class)->only(['index', 'show']);
Route::get('item-criteria', [ItemCriteriaController::class, 'index']);
Route::get('items/{item}/criteria', [ItemCriteriaController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Authorization handled by policies/form requests
    Route::post('/users', [UsersController::class, 'store']);
    Route::put('/users/{user}', [UsersController::class, 'update']);
    Route::patch('/users/{user}', [UsersController::class, 'update']);
    Route::delete('/users/{user}', [UsersController::class, 'destroy']);

    Route::apiResource('collections', CollectionsController::class)->except(['index', 'show']);
    Route::apiResource('items', ItemsController::class)->except(['index', 'show']);

    // Role-based barrier stays in the route because access depends only on user_type
    Route::middleware('user_type:admin,editor')->group(function () {
        Route::apiResource('categories', CategoryController::class)->except(['index', 'show']);
        Route::apiResource('criteria', CriteriaController::class)->except(['index', 'show']);
    });

    Route::post('item-criteria', [ItemCriteriaController::class, 'store']);
    Route::put('items/{item}/criteria/{criterion}', [ItemCriteriaController::class, 'update']);
    Route::delete('items/{item}/criteria/{criterion}', [ItemCriteriaController::class, 'destroy']);
});
