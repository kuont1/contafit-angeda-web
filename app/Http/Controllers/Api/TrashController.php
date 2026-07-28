<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrashController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $trashedEvents = Event::onlyTrashed()
            ->where('user_id', $request->user()->id)
            ->orderBy('deleted_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'events' => $trashedEvents,
            ],
            'message' => 'Eventos en papelera recuperados correctamente.',
        ]);
    }

    public function restore(Request $request, int $id): JsonResponse
    {
        $event = Event::onlyTrashed()
            ->where('user_id', $request->user()->id)
            ->where('id', $id)
            ->first();

        if (! $event) {
            return response()->json([
                'success' => false,
                'message' => 'Evento no encontrado en la papelera.',
            ], 404);
        }

        $event->restore();

        return response()->json([
            'success' => true,
            'data' => [
                'event' => $event,
            ],
            'message' => 'Evento restaurado correctamente.',
        ]);
    }

    public function forceDelete(Request $request, int $id): JsonResponse
    {
        $event = Event::onlyTrashed()
            ->where('user_id', $request->user()->id)
            ->where('id', $id)
            ->first();

        if (! $event) {
            return response()->json([
                'success' => false,
                'message' => 'Evento no encontrado en la papelera.',
            ], 404);
        }

        $event->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'Evento eliminado de forma definitiva.',
        ]);
    }
}
