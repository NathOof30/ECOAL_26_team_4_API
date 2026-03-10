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

/*
|--------------------------------------------------------------------------
| Valid API Routes List
|--------------------------------------------------------------------------
|
| All routes below are automatically prefixed with /api
| Example base URL: http://127.0.0.1:8000/api
|
| --- AUTHENTICATION ---
| POST   /register           : Create an account            - need to be checked
| POST   /login              : Log in (retrieve token)      - need to be checked
| POST   /logout             : Log out (delete token)       - (Requires Auth)
| GET    /user               : Get the logged in user       - (Requires Auth)
|
| --- USERS ---
| GET    /users              : List of users                - checked OK (Public)
| GET    /users/{id}         : Details of a user            - checked OK (Public)
| POST, PUT, DELETE          : (Locked or to be managed)    - (Requires Auth)
|
| --- COLLECTIONS ---
| GET    /collections        : List of collections          - checked OK (Public)
| GET    /collections/{id}   : Details of a collection      - checked OK (Public)
| POST   /collections        : Create a collection          - IMPROVED: Uses auth()->user()->id automatically (Requires Auth)
| PUT, DELETE /collections/{id}: Update/Delete a collection - (Requires Auth)
|
| --- CATEGORIES ---
| GET    /categories         : List of categories           - IMPROVED: timestamps hidden ! (Public)
| GET    /categories/{id}    : Details of a category        - checked OK (Public)
| POST, PUT, DELETE          : Manage categories            - (Requires Auth - ideally Admin account)
|
| --- ITEMS ---
| GET    /items              : List of items                - checked OK (Public)
| GET    /items/{id}         : Details of an item           - checked OK (Public)
| POST   /items              : Create an item               - IMPROVED: Assigns automatically to user's collection (Requires Auth)
| PUT, DELETE /items/{id}    : Update/Delete an item        - (Requires Auth)
|
| --- CRITERIA ---
| GET    /criteria           : List of criteria             - checked OK (Public)
| GET    /criteria/{id}      : Details of a criterion       - checked OK (Public)
| POST, PUT, DELETE          : Manage criteria              - (Requires Auth - ideally Admin account)
|
| --- ITEM CRITERIA (SCORES) ---
| GET    /item-criteria                     : List of scores                 - checked OK (Public)
| GET    /items/{item_id}/criteria          : Specific scores for an item    - checked OK (Public)
| POST   /item-criteria                     : Assign a score                 - [need to be checked/improved] (Requires Auth)
| PUT    /items/{item_id}/criteria/{crit_id}: Update a score                 - [need to be checked/improved] (Requires Auth)
| DELETE /items/{item_id}/criteria/{crit_id}: Delete a score                 - [need to be checked/improved] (Requires Auth)
|
*/

// ==========================================
// 1. PUBLIC ROUTES (No Token Required)
// ==========================================

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public reading (GET only)
Route::apiResource('users', UsersController::class)->only(['index', 'show']);
Route::apiResource('collections', CollectionsController::class)->only(['index', 'show']);
Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);
Route::apiResource('items', ItemsController::class)->only(['index', 'show']);
Route::apiResource('criteria', CriteriaController::class)->only(['index', 'show']);

// Scores - Public reading
Route::get('item-criteria', [ItemCriteriaController::class, 'index']);
Route::get('items/{item}/criteria', [ItemCriteriaController::class, 'show']);

// ==========================================
// 2. PROTECTED ROUTES (Require Bearer Token)
// ==========================================

Route::middleware('auth:sanctum')->group(function () {

    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/user/avatar', [UsersController::class, 'uploadAvatar']);

    Route::post('items/{item}/image', [ItemsController::class, 'uploadImage']);

    // Protected writing (POST, PUT, DELETE)
    Route::post('users', [UsersController::class, 'store'])->middleware('admin');
    Route::put('users/{user}', [UsersController::class, 'update']);
    Route::patch('users/{user}', [UsersController::class, 'update']);
    Route::delete('users/{user}', [UsersController::class, 'destroy']);

    Route::apiResource('collections', CollectionsController::class)->except(['index', 'show']);
    Route::apiResource('items', ItemsController::class)->except(['index', 'show']);

    Route::middleware('admin')->group(function () {
        Route::apiResource('categories', CategoryController::class)->except(['index', 'show']);
        Route::apiResource('criteria', CriteriaController::class)->except(['index', 'show']);
    });

    // Scores - Protected writing
    Route::post('item-criteria', [ItemCriteriaController::class, 'store']);
    Route::put('items/{item}/criteria/{criterion}', [ItemCriteriaController::class, 'update']);
    Route::delete('items/{item}/criteria/{criterion}', [ItemCriteriaController::class, 'destroy']);

});
