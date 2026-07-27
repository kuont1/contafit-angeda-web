<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendNotificationJob;
use App\Models\Event;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    /**
     * RF-07: Listar eventos con filtros avanzados y búsqueda (solo eventos no eliminados).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Event::where('user_id', $request->user()->id);

        // Búsqueda por palabra clave en title o description (LIKE / ILIKE)
        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        // Filtro por rango de fechas (start_date / end_date)
        if ($request->filled('start_date')) {
            $query->whereDate('start_at', '>=', $request->query('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('start_at', '<=', $request->query('end_date'));
        }

        // Filtro por tipo (tarea, recordatorio, fecha_importante)
        if ($request->filled('type')) {
            $query->where('type', $request->query('type'));
        }

        // Filtro por estado (pendiente, en_progreso, completada)
        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        $events = $query->orderBy('start_at')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'events' => $events,
            ],
            'message' => 'Eventos recuperados correctamente.',
        ]);
    }

    /**
     * RF-06 & Store: Crear evento.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'string', 'in:tarea,recordatorio,fecha_importante'],
            'start_at' => ['required', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'color' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'string', 'in:pendiente,en_progreso,completada'],
            'is_recurring' => ['nullable', 'boolean'],
            'recurrence_frequency' => ['required_if:is_recurring,true', 'nullable', 'string', 'in:diaria,semanal,mensual,anual'],
            'reminder_minutes_before' => ['nullable', 'integer'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación.',
                'data' => [
                    'errors' => $validator->errors(),
                ],
            ], 422);
        }

        $isRecurring = (bool) $request->input('is_recurring', false);

        $event = Event::create([
            'user_id' => $request->user()->id,
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'type' => $request->input('type'),
            'start_at' => $request->input('start_at'),
            'end_at' => $request->input('end_at'),
            'color' => $request->input('color', '#3B82F6'),
            'status' => $request->input('status', 'pendiente'),
            'is_recurring' => $isRecurring,
            'recurrence_frequency' => $isRecurring ? $request->input('recurrence_frequency') : null,
            'reminder_minutes_before' => $request->input('reminder_minutes_before'),
        ]);

        // Programar notificación automática y despachar Job asíncrono (RF-10 / ADR-006)
        $scheduledAt = (new \App\Services\NotificationService())->calculateScheduledTime($event);

        Notification::create([
            'event_id' => $event->id,
            'user_id' => $event->user_id,
            'scheduled_at' => $scheduledAt,
            'status' => 'pendiente',
        ]);

        if ($scheduledAt->isFuture()) {
            SendNotificationJob::dispatch($event)->delay($scheduledAt);
        } else {
            SendNotificationJob::dispatch($event);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'event' => $event,
            ],
            'message' => 'Evento creado correctamente.',
        ], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $event = Event::find($id);

        if (! $event) {
            return response()->json([
                'success' => false,
                'message' => 'Evento no encontrado.',
            ], 404);
        }

        if ($event->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para acceder a este evento.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'event' => $event,
            ],
            'message' => 'Evento obtenido correctamente.',
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $event = Event::find($id);

        if (! $event) {
            return response()->json([
                'success' => false,
                'message' => 'Evento no encontrado.',
            ], 404);
        }

        if ($event->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para acceder a este evento.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['sometimes', 'required', 'string', 'in:tarea,recordatorio,fecha_importante'],
            'start_at' => ['sometimes', 'required', 'date'],
            'end_at' => ['nullable', 'date'],
            'color' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'string', 'in:pendiente,en_progreso,completada'],
            'is_recurring' => ['nullable', 'boolean'],
            'recurrence_frequency' => ['required_if:is_recurring,true', 'nullable', 'string', 'in:diaria,semanal,mensual,anual'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación.',
                'data' => [
                    'errors' => $validator->errors(),
                ],
            ], 422);
        }

        $event->update($request->only([
            'title', 'description', 'type', 'start_at', 'end_at', 'color', 'status',
            'is_recurring', 'recurrence_frequency'
        ]));

        return response()->json([
            'success' => true,
            'data' => [
                'event' => $event,
            ],
            'message' => 'Evento actualizado correctamente.',
        ]);
    }

    /**
     * RF-06: Modificar únicamente una fecha/ocurrencia específica.
     * PUT/PATCH /api/events/{id}/instance
     */
    public function updateInstance(Request $request, int $id): JsonResponse
    {
        $parentEvent = Event::find($id);

        if (! $parentEvent) {
            return response()->json([
                'success' => false,
                'message' => 'Evento base no encontrado.',
            ], 404);
        }

        if ($parentEvent->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para acceder a este evento.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['sometimes', 'required', 'string', 'in:tarea,recordatorio,fecha_importante'],
            'start_at' => ['required', 'date'],
            'end_at' => ['nullable', 'date'],
            'color' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'string', 'in:pendiente,en_progreso,completada'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación.',
                'data' => [
                    'errors' => $validator->errors(),
                ],
            ], 422);
        }

        // Crear registro específico para la ocurrencia vinculada al padre
        $instance = Event::create([
            'user_id' => $request->user()->id,
            'title' => $request->input('title', $parentEvent->title),
            'description' => $request->input('description', $parentEvent->description),
            'type' => $request->input('type', $parentEvent->type),
            'start_at' => $request->input('start_at'),
            'end_at' => $request->input('end_at', $parentEvent->end_at),
            'color' => $request->input('color', $parentEvent->color),
            'status' => $request->input('status', $parentEvent->status),
            'is_recurring' => false,
            'recurrence_parent_id' => $parentEvent->id,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'event' => $instance,
            ],
            'message' => 'Instancia del evento modificada correctamente.',
        ]);
    }

    /**
     * RF-06: Ocultar/Eliminar únicamente la ocurrencia de una fecha específica sin borrar la serie padre.
     * DELETE /api/events/{id}/instance
     */
    public function destroyInstance(Request $request, int $id): JsonResponse
    {
        $event = Event::withTrashed()->find($id);

        if (! $event) {
            return response()->json([
                'success' => false,
                'message' => 'Instancia de evento no encontrada.',
            ], 404);
        }

        if ($event->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para acceder a este evento.',
            ], 403);
        }

        // Si es una ocurrencia hijo previamente creada (recurrence_parent_id !== null) o evento NO recurrente, lo eliminamos directamente (soft delete)
        if ($event->recurrence_parent_id !== null || ! $event->is_recurring) {
            $event->delete();

            return response()->json([
                'success' => true,
                'message' => 'Instancia del evento eliminada correctamente.',
            ]);
        }

        // Si es un evento padre recurrente, leemos la fecha enviada por query o body para ocultarla (status = 'excluded')
        $date = $request->query('date', $request->input('date'));

        if (! $date) {
            $date = Carbon::parse($event->start_at)->toDateString();
        }

        $timeStr = Carbon::parse($event->start_at)->toTimeString();

        // Ocultamiento lógico: Crear una marca de exclusión activa (status = 'excluded') asignada a esa fecha concreta
        Event::create([
            'user_id' => $request->user()->id,
            'title' => $event->title,
            'description' => $event->description,
            'type' => $event->type,
            'start_at' => "{$date} {$timeStr}",
            'end_at' => $event->end_at ? "{$date} " . Carbon::parse($event->end_at)->toTimeString() : null,
            'color' => $event->color,
            'status' => 'excluded',
            'is_recurring' => false,
            'recurrence_parent_id' => $event->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Instancia del evento eliminada correctamente.',
        ]);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $event = Event::find($id);

        if (! $event) {
            return response()->json([
                'success' => false,
                'message' => 'Evento no encontrado.',
            ], 404);
        }

        if ($event->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para acceder a este evento.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => ['required', 'string', 'in:pendiente,en_progreso,completada'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación.',
                'data' => [
                    'errors' => $validator->errors(),
                ],
            ], 422);
        }

        $newStatus = $request->input('status');
        $completedAt = ($newStatus === 'completada') ? Carbon::now() : null;

        $event->update([
            'status' => $newStatus,
            'completed_at' => $completedAt,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'event' => $event->fresh(),
            ],
            'message' => 'Estado de evento actualizado correctamente.',
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $event = Event::find($id);

        if (! $event) {
            return response()->json([
                'success' => false,
                'message' => 'Evento no encontrado.',
            ], 404);
        }

        if ($event->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para acceder a este evento.',
            ], 403);
        }

        // Eliminar (soft delete) también instancias o exclusiones hijas de este evento padre
        Event::where('recurrence_parent_id', $event->id)->delete();
        $event->delete();

        return response()->json([
            'success' => true,
            'message' => 'Evento eliminado correctamente.',
        ]);
    }
}
