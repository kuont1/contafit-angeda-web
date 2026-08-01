<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendNotificationJob;
use App\Models\Event;
use App\Models\Notification;
use App\Services\NotificationService;
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
        // Procesar notificaciones pendientes acumuladas
        (new NotificationService)->processPendingNotifications();

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

    public function store(Request $request): JsonResponse
    {
        $input = $request->all();
        if (isset($input['end_at']) && (trim((string) $input['end_at']) === '')) {
            $input['end_at'] = null;
        }

        $messages = [
            'title.required' => 'El título del evento es obligatorio.',
            'type.required' => 'El tipo de evento es obligatorio.',
            'type.in' => 'El tipo de evento seleccionado no es válido.',
            'start_at.required' => 'La fecha y hora de inicio es obligatoria.',
            'start_at.date' => 'La fecha de inicio debe ser una fecha válida.',
            'end_at.after_or_equal' => 'La fecha/hora de fin debe ser igual o posterior a la fecha/hora de inicio.',
            'end_at.date' => 'La fecha de fin debe ser una fecha válida.',
            'recurrence_frequency.required_if' => 'La frecuencia de repetición es obligatoria para eventos recurrentes.',
        ];

        $validator = Validator::make($input, [
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
        ], $messages);

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

        // Programar notificación automática y despachar (RF-10 / ADR-006)
        $notificationService = new NotificationService;
        $scheduledAt = $notificationService->calculateScheduledTime($event);

        Notification::updateOrCreate(
            ['event_id' => $event->id, 'user_id' => $event->user_id],
            [
                'scheduled_at' => $scheduledAt,
                'status' => 'pendiente',
            ]
        );

        SendNotificationJob::dispatch($event)->delay($scheduledAt);

        if ($scheduledAt->isPast() || $scheduledAt->lessThanOrEqualTo(now()->addMinute())) {
            $notificationService->sendAlert($event);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'event' => $event,
            ],
            'message' => 'Evento creado correctamente.',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
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
            'message' => 'Evento recuperado correctamente.',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
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

        $input = $request->all();
        if (isset($input['start_at']) && isset($input['end_at'])) {
            if ($input['end_at'] < $input['start_at']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación.',
                    'data' => [
                        'errors' => [
                            'end_at' => ['La fecha/hora de fin debe ser igual o posterior a la fecha/hora de inicio.'],
                        ],
                    ],
                ], 422);
            }
        }

        $messages = [
            'type.in' => 'El tipo de evento debe ser uno de: tarea, recordatorio, fecha_importante.',
            'end_at.after_or_equal' => 'La fecha/hora de fin debe ser igual o posterior a la fecha/hora de inicio.',
            'end_at.date' => 'La fecha de fin debe ser una fecha válida.',
            'recurrence_frequency.required_if' => 'La frecuencia de repetición es obligatoria para eventos recurrentes.',
        ];

        $validator = Validator::make($input, [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['sometimes', 'required', 'string', 'in:tarea,recordatorio,fecha_importante'],
            'start_at' => ['sometimes', 'required', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'color' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'string', 'in:pendiente,en_progreso,completada'],
            'is_recurring' => ['nullable', 'boolean'],
            'recurrence_frequency' => ['required_if:is_recurring,true', 'nullable', 'string', 'in:diaria,semanal,mensual,anual'],
            'reminder_minutes_before' => ['nullable', 'integer', 'min:0'],
        ], $messages);

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
            'is_recurring', 'recurrence_frequency', 'reminder_minutes_before',
        ]));

        // Actualizar o reprogramar notificación para el evento
        $notificationService = new NotificationService;
        $scheduledAt = $notificationService->calculateScheduledTime($event);

        Notification::updateOrCreate(
            ['event_id' => $event->id, 'user_id' => $event->user_id],
            [
                'scheduled_at' => $scheduledAt,
                'status' => 'pendiente',
            ]
        );

        SendNotificationJob::dispatch($event)->delay($scheduledAt);

        if ($scheduledAt->isPast() || $scheduledAt->lessThanOrEqualTo(now()->addMinute())) {
            $notificationService->sendAlert($event);
        }

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

        // Soft delete children instances too
        Event::where('recurrence_parent_id', $event->id)->delete();

        $event->delete();

        return response()->json([
            'success' => true,
            'message' => 'Evento eliminado correctamente.',
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

        // Ocultamiento lógico (RF-06): Crear registro de exclusión activo (status = 'excluded')
        $instance = Event::create([
            'user_id' => $request->user()->id,
            'title' => $event->title." (Ocurrencia {$date})",
            'description' => $event->description,
            'type' => $event->type,
            'start_at' => "{$date} {$timeStr}",
            'end_at' => $event->end_at ? "{$date} ".Carbon::parse($event->end_at)->toTimeString() : null,
            'color' => $event->color,
            'status' => 'excluded',
            'is_recurring' => false,
            'recurrence_parent_id' => $event->id,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'event' => $instance,
            ],
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
}
