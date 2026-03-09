<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\UsersController;
use App\Http\Controllers\API\CollectionsController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\ItemsController;
use App\Http\Controllers\API\CriteriaController;
use App\Http\Controllers\API\ItemCriteriaController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| All routes are prefixed with /api automatically by Laravel.
| Example: /api/users, /api/collections, /api/items, etc.
|
*/

// Default Sanctum route for authenticated user
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// RESTful API resource routes for all entities
Route::apiResource('users', UsersController::class);
Route::apiResource('collections', CollectionsController::class);
Route::apiResource('categories', CategoryController::class);
Route::apiResource('items', ItemsController::class);
Route::apiResource('criteria', CriteriaController::class);

// Item criteria scores (pivot table) — custom routes
Route::get('item-criteria', [ItemCriteriaController::class, 'index']);
Route::post('item-criteria', [ItemCriteriaController::class, 'store']);
Route::get('items/{item}/criteria', [ItemCriteriaController::class, 'show']);
Route::put('items/{item}/criteria/{criterion}', [ItemCriteriaController::class, 'update']);
Route::delete('items/{item}/criteria/{criterion}', [ItemCriteriaController::class, 'destroy']);
