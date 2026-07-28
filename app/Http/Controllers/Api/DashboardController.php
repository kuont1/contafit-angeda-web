<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function today(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Permite recibir la fecha local enviada por la SPA o usa la fecha de Ecuador (America/Guayaquil)
        $dateInput = $request->query('date');
        $today = $dateInput ? Carbon::parse($dateInput) : Carbon::today('America/Guayaquil');

        // 1. Eventos directos del día evaluado (excluyendo fechas_importantes y completadas)
        $directEvents = Event::where('user_id', $user->id)
            ->whereDate('start_at', $today)
            ->where('type', '!=', 'fecha_importante')
            ->where('status', '!=', 'completada')
            ->whereNull('completed_at')
            ->get();

        // 2. Eventos recurrentes activos que aplican al día evaluado
        $recurringEvents = Event::where('user_id', $user->id)
            ->where('is_recurring', true)
            ->where('type', '!=', 'fecha_importante')
            ->whereDate('start_at', '<=', $today)
            ->where('status', '!=', 'completada')
            ->whereNull('completed_at')
            ->get()
            ->filter(function ($event) use ($today) {
                $startDate = Carbon::parse($event->start_at);

                return match ($event->recurrence_frequency) {
                    'diaria' => true,
                    'semanal' => $startDate->dayOfWeek === $today->dayOfWeek,
                    'mensual' => $startDate->day === $today->day,
                    'anual' => $startDate->month === $today->month && $startDate->day === $today->day,
                    default => false,
                };
            });

        // Combinar y eliminar duplicados por ID
        $allTodayEvents = $directEvents->concat($recurringEvents)
            ->unique('id')
            ->sortBy('start_at')
            ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'events' => $allTodayEvents,
                'date' => $today->toDateString(),
            ],
            'message' => 'Eventos de hoy recuperados correctamente.',
        ]);
    }
}
