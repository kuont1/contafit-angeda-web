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
        $userId = $request->user()->id;

        // Recuperar eventos eliminados suavemente y marcas de exclusión activas en papelera
        $trashedEvents = Event::withTrashed()
            ->where('user_id', $userId)
            ->where(function ($query) {
                // Eventos principales borrados suavemente (deleted_at != null y status != excluded)
                $query->where(function ($q) {
                    $q->whereNotNull('deleted_at')
                        ->where('status', '!=', 'excluded');
                })
                // O marcas de exclusión visibles en papelera (deleted_at null y status = excluded)
                    ->orWhere(function ($q) {
                        $q->whereNull('deleted_at')
                            ->where('status', 'excluded');
                    });
            })
            ->orderBy('updated_at', 'desc')
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
        $userId = $request->user()->id;

        $event = Event::withTrashed()
            ->where('user_id', $userId)
            ->where('id', $id)
            ->first();

        if (! $event) {
            return response()->json([
                'success' => false,
                'message' => 'Evento no encontrado en la papelera.',
            ], 404);
        }

        if ($event->status === 'excluded') {
            // Eliminar definitivamente la marca de exclusión para restablecer la ocurrencia en el calendario
            $event->forceDelete();
        } else {
            $event->restore();
            Event::onlyTrashed()->where('recurrence_parent_id', $event->id)->restore();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'event' => $event,
            ],
            'message' => 'Evento o fecha restaurada correctamente.',
        ]);
    }

    public function forceDelete(Request $request, int $id): JsonResponse
    {
        $userId = $request->user()->id;

        $event = Event::withTrashed()
            ->where('user_id', $userId)
            ->where('id', $id)
            ->first();

        if (! $event) {
            return response()->json([
                'success' => false,
                'message' => 'Evento no encontrado en la papelera.',
            ], 404);
        }

        if ($event->status === 'excluded') {
            // Se envía a soft-delete (deleted_at = now()) para desaparecer de la papelera manteniendo la fecha oculta permanentemente
            $event->delete();
        } else {
            Event::withTrashed()->where('recurrence_parent_id', $event->id)->forceDelete();
            $event->forceDelete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Evento eliminado de forma definitiva.',
        ]);
    }
}
