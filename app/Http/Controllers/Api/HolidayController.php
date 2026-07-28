<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use App\Services\FeriadosService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function index(Request $request, FeriadosService $feriadosService): JsonResponse
    {
        $year = $request->query('year') ? (int) $request->query('year') : (int) date('Y');
        $forceSync = $request->boolean('force');

        $holidays = Holiday::whereYear('date', $year)->orderBy('date')->get();

        // Si no existen feriados cargados para el año, o si se solicita forzar sincronización en vivo
        if ($holidays->isEmpty() || $forceSync) {
            $holidays = $feriadosService->syncHolidays($year);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'holidays' => $holidays,
            ],
            'message' => 'Feriados recuperados correctamente.',
        ]);
    }
}
