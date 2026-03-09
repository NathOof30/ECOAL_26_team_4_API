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
| Valid API Routes List
|--------------------------------------------------------------------------
|
| All routes below are automatically prefixed with /api
| Example base URL: http://127.0.0.1:8000/api
|
| --- USERS ---
| GET    /users              : Get a list of all users      - checked OK
| POST   /users              : Create a new user            - checked OK
| GET    /users/{id}         : Get specific user details    - checked OK
| PUT    /users/{id}         : Update a specific user       - checked OK
| DELETE /users/{id}         : Delete a specific user       - checked OK
|
| --- COLLECTIONS ---
| GET    /collections        : Get a list of all collections    - checked OK
| POST   /collections        : Create a new collection          - how ? user must be connected -> give the Bearer token ?          
| GET    /collections/{id}   : Get specific collection details  - checked OK
| PUT    /collections/{id}   : Update a specific collection     - checked OK
| DELETE /collections/{id}   : Delete a specific collection     - checked OK
|
| --- CATEGORIES ---
| GET    /categories         : Get a list of all categories     - checked OK -> delete timestamp ?
| POST   /categories         : Create a new category            - checked OK
| GET    /categories/{id}    : Get specific category details    - checked OK
| PUT    /categories/{id}    : Update a specific category       - checked OK
| DELETE /categories/{id}    : Delete a specific category       - checked OK
|
| --- ITEMS ---
| GET    /items              : Get a list of all items          - checked OK
| POST   /items              : Create a new item                - user must be connected -> to connect with collection... and on created -> call item-criteria to add scores -> depend on the form ?     
| GET    /items/{id}         : Get specific item details        - checked OK
| PUT    /items/{id}         : Update a specific item           - checked OK
| DELETE /items/{id}         : Delete a specific item           - checked OK
|
| --- CRITERIA ---
| GET    /criteria           : Get a list of all criteria       - checked OK
| POST   /criteria           : Create a new criterion           - checked OK
| GET    /criteria/{id}      : Get specific criterion details   - checked OK
| PUT    /criteria/{id}      : Update a specific criterion      - checked OK
| DELETE /criteria/{id}      : Delete a specific criterion      - checked OK
|
| --- ITEM CRITERIA (SCORES) ---
| GET    /item-criteria                     : Get all item-criteria records                     - checked OK -> too much infos but ok
| POST   /item-criteria                     : Assign a score to an item for a criterion         - 
| GET    /items/{item_id}/criteria          : Get all criteria and scores for a specific item   - checked OK
| PUT    /items/{item_id}/criteria/{crit_id}: Update score for a specific item's criterion      - 
| DELETE /items/{item_id}/criteria/{crit_id}: Delete a score for a specific item's criterion    - 
|
|--------------------------------------------------------------------------
| New routes to execute below:
|--------------------------------------------------------------------------
|
|
|
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
