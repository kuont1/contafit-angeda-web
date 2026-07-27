<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function today(Request $request): JsonResponse
    {
        $events = $request->user()
            ->events()
            ->whereIn('type', ['tarea', 'recordatorio'])
            ->whereDate('start_at', Carbon::today())
            ->where('status', '!=', 'completada')
            ->orderBy('start_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'events' => $events,
            ],
            'message' => 'Resumen del día obtenido correctamente.',
        ]);
    }
}