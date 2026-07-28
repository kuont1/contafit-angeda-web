<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\HolidayController;
use App\Http\Controllers\Api\TrashController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::patch('/user/settings', [AuthController::class, 'updateSettings']);
    Route::delete('/account', [AuthController::class, 'deleteAccount']);

    // Eventos
    Route::get('/events', [EventController::class, 'index']);
    Route::post('/events', [EventController::class, 'store']);
    Route::get('/events/{id}', [EventController::class, 'show']);
    Route::patch('/events/{id}', [EventController::class, 'update']);
    Route::put('/events/{id}', [EventController::class, 'update']);
    Route::patch('/events/{id}/status', [EventController::class, 'updateStatus']);
    Route::delete('/events/{id}', [EventController::class, 'destroy']);

    // RF-06: Instancias individuales de eventos recurrentes
    Route::match(['put', 'patch'], '/events/{id}/instance', [EventController::class, 'updateInstance']);
    Route::delete('/events/{id}/instance', [EventController::class, 'destroyInstance']);

    // RF-11: Papelera de Reciclaje
    Route::get('/trash', [TrashController::class, 'index']);
    Route::post('/trash/{id}/restore', [TrashController::class, 'restore']);
    Route::delete('/trash/{id}/force', [TrashController::class, 'forceDelete']);

    // Dashboard
    Route::get('/dashboard/today', [DashboardController::class, 'today']);

    // Feriados
    Route::get('/holidays', [HolidayController::class, 'index']);
});
