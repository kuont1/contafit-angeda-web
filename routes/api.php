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
    
    // RF-03: Eliminación Definitiva de Cuenta (Rutas /profile y /account)
    Route::post('/account/send-deletion-code', [AuthController::class, 'sendDeletionCode']);
    Route::delete('/account', [AuthController::class, 'deleteAccount']);
    Route::delete('/profile', [AuthController::class, 'deleteAccount']);

    // RF-11: Papelera de Reciclaje y Purga (Rutas /events/trash y /trash)
    Route::get('/events/trash', [TrashController::class, 'index']);
    Route::get('/trash', [TrashController::class, 'index']);
    
    Route::post('/events/{id}/restore', [TrashController::class, 'restore']);
    Route::post('/trash/{id}/restore', [TrashController::class, 'restore']);

    Route::delete('/events/{id}/force-delete', [TrashController::class, 'forceDelete']);
    Route::delete('/trash/{id}/force', [TrashController::class, 'forceDelete']);

    // Eventos (CRUD Principal)
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

    // Dashboard
    Route::get('/dashboard/today', [DashboardController::class, 'today']);

    // Feriados (RF-09)
    Route::get('/holidays', [HolidayController::class, 'index']);
});
