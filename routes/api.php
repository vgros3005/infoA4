<?php

use App\Http\Controllers\Api\RequestApiController;
use App\Http\Controllers\Api\TaskApiController;
use App\Http\Controllers\Api\PriorityApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| RESTful API routes for the infoA4 application.
| All routes require authentication via Sanctum token.
|
*/

Route::middleware(['auth:sanctum'])->group(function () {

    // --- Requests A4 ---
    Route::get('/requests',     [RequestApiController::class, 'index']);
    Route::get('/requests/{id}', [RequestApiController::class, 'show']);

    // --- Tasks (Gantt) ---
    Route::get('/tasks/gantt-data',         [TaskApiController::class, 'ganttData']);
    Route::get('/tasks/by-user/{userId}',   [TaskApiController::class, 'byUser']);

    // --- Priorities ---
    Route::get('/priorities/{id}/requires-justification', [PriorityApiController::class, 'requiresJustification']);
});
