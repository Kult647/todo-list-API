<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TagController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);

    Route::apiResource('tasks', TaskController::class);
    Route::post('/tasks/{task}/restore', [TaskController::class, 'restore'])
        ->withTrashed();

    Route::apiResource('projects', ProjectController::class);
    Route::post('/projects/{project}/add-user', [ProjectController::class, 'addUser']);
    Route::post('/projects/{project}/remove-user', [ProjectController::class, 'removeUser']);

    Route::apiResource('tags', TagController::class);
});
