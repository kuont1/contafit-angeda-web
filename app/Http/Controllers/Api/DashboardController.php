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
        
        $dateInput = $request->query('date');
        $today = $dateInput ? Carbon::parse($dateInput) : Carbon::today('America/Guayaquil');
        $includeCompleted = $request->boolean('include_completed', false);

        // 1. Eventos directos del día evaluado (excluyendo fechas_importantes)
        $directEventsQuery = Event::where('user_id', $user->id)
            ->whereDate('start_at', $today)
            ->where('type', '!=', 'fecha_importante');

        if (! $includeCompleted) {
            $directEventsQuery->where('status', '!=', 'completada')->whereNull('completed_at');
        }

        $directEvents = $directEventsQuery->get();

        // 2. Eventos recurrentes activos que aplican al día evaluado
        $recurringEventsQuery = Event::where('user_id', $user->id)
            ->where('is_recurring', true)
            ->where('type', '!=', 'fecha_importante')
            ->whereDate('start_at', '<=', $today);

        if (! $includeCompleted) {
            $recurringEventsQuery->where('status', '!=', 'completada')->whereNull('completed_at');
        }

        $recurringEvents = $recurringEventsQuery->get()
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
