<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API v1 Project Routes
|--------------------------------------------------------------------------
|
| Here are the project management routes for API version 1.
| All routes are protected with authentication middleware.
|
*/

// Public authentication routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])
        ->middleware('throttle:5,1'); // 5 attempts per minute
    
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1'); // 5 attempts per minute
});

// Protected authentication routes
Route::middleware(['auth:sanctum'])->prefix('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/me', [AuthController::class, 'me']);
});

// Protected user routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

// Protected project routes
Route::middleware(['auth:sanctum'])->prefix('projects')->group(function () {
    // Basic CRUD operations
    Route::get('/', [ProjectController::class, 'index']);
    Route::post('/', [ProjectController::class, 'store']);
    Route::get('/statistics', [ProjectController::class, 'statistics']);
    Route::get('/search', [ProjectController::class, 'search']);
    Route::get('/status/{status}', [ProjectController::class, 'byStatus']);
    Route::get('/overdue', [ProjectController::class, 'overdue']);
    Route::get('/{project}', [ProjectController::class, 'show']);
    Route::put('/{project}', [ProjectController::class, 'update']);
    Route::patch('/{project}', [ProjectController::class, 'update']);
    Route::delete('/{project}', [ProjectController::class, 'destroy']);
    
    // Additional operations
    Route::post('/{id}/restore', [ProjectController::class, 'restore']);
    Route::delete('/{id}/force-delete', [ProjectController::class, 'forceDelete']);
    Route::post('/bulk-update-status', [ProjectController::class, 'bulkUpdateStatus']);
    Route::post('/{project}/duplicate', [ProjectController::class, 'duplicate']);
});