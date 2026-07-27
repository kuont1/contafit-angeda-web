<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class EventController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $events = $request->user()
            ->events()
            ->orderBy('start_at')
            ->get();

        return $this->successResponse([
            'events' => $events,
        ], 'Eventos obtenidos correctamente.');
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), $this->storeRules());

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->toArray());
        }

        $data = $validator->validated();
        $startAt = $this->parseEventDate($data['start_at']);
        $endAt = $this->parseNullableEventDate($data['end_at'] ?? null);

        if ($endAt !== null && $endAt->lessThanOrEqualTo($startAt)) {
            return $this->validationErrorResponse([
                'end_at' => ['El campo end_at debe ser una fecha posterior a start_at.'],
            ]);
        }

        $event = $request->user()->events()->create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'type' => $data['type'],
            'start_at' => $startAt,
            'end_at' => $endAt,
            'color' => $data['color'] ?? null,
            'status' => $data['status'] ?? 'pendiente',
            'completed_at' => $this->resolveCompletedAt(
                $data['type'],
                $data['status'] ?? 'pendiente'
            ),
        ]);

        return $this->successResponse([
            'event' => $event->fresh(),
        ], 'Evento creado correctamente.', 201);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $event = $this->resolveAccessibleEvent($request, $id);

        if ($event instanceof JsonResponse) {
            return $event;
        }

        return $this->successResponse([
            'event' => $event,
        ], 'Evento obtenido correctamente.');
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $event = $this->resolveAccessibleEvent($request, $id);

        if ($event instanceof JsonResponse) {
            return $event;
        }

        $validator = Validator::make($request->all(), $this->updateRules($request));

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->toArray());
        }

        $data = $validator->validated();
        $effectiveType = $data['type'] ?? $event->type;
        $effectiveStartAt = array_key_exists('start_at', $data)
            ? $this->parseEventDate($data['start_at'])
            : $event->start_at;
        $effectiveEndAt = array_key_exists('end_at', $data)
            ? $this->parseNullableEventDate($data['end_at'])
            : $event->end_at;

        if ($effectiveEndAt !== null && $effectiveEndAt->lessThanOrEqualTo($effectiveStartAt)) {
            return $this->validationErrorResponse([
                'end_at' => ['El campo end_at debe ser una fecha posterior a start_at.'],
            ]);
        }

        $payload = [];

        foreach (['title', 'description', 'type', 'color'] as $field) {
            if (array_key_exists($field, $data)) {
                $payload[$field] = $data[$field];
            }
        }

        if (array_key_exists('start_at', $data)) {
            $payload['start_at'] = $effectiveStartAt;
        }

        if (array_key_exists('end_at', $data)) {
            $payload['end_at'] = $effectiveEndAt;
        }

        if (array_key_exists('status', $data)) {
            $payload['status'] = $data['status'];
            $payload['completed_at'] = $this->resolveCompletedAt($effectiveType, $data['status']);
        }

        $event->fill($payload)->save();

        return $this->successResponse([
            'event' => $event->fresh(),
        ], 'Evento actualizado correctamente.');
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $event = $this->resolveAccessibleEvent($request, $id);

        if ($event instanceof JsonResponse) {
            return $event;
        }

        $event->delete();

        return $this->successResponse(null, 'Evento eliminado correctamente.');
    }

    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $event = $this->resolveAccessibleEvent($request, $id);

        if ($event instanceof JsonResponse) {
            return $event;
        }

        $validator = Validator::make($request->all(), [
            'status' => ['required', 'string', Rule::in(['pendiente', 'en_progreso', 'completada'])],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->toArray());
        }

        $status = $validator->validated()['status'];

        $event->status = $status;
        $event->completed_at = $status === 'completada' ? Carbon::now() : null;
        $event->save();

        return $this->successResponse([
            'event' => $event->fresh(),
        ], 'Estado del evento actualizado correctamente.');
    }

    private function storeRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'type' => ['required', 'string', Rule::in(['tarea', 'recordatorio', 'fecha_importante'])],
            'start_at' => ['required', 'string', $this->eventDateRule()],
            'end_at' => ['sometimes', 'nullable', 'string', $this->eventDateRule()],
            'color' => ['sometimes', 'nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'status' => ['sometimes', 'nullable', 'string', Rule::in(['pendiente', 'en_progreso', 'completada'])],
        ];
    }

    private function updateRules(Request $request): array
    {
        $requiredRule = $request->isMethod('put') ? 'required' : 'sometimes';

        return [
            'title' => [$requiredRule, 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'type' => [$requiredRule, 'string', Rule::in(['tarea', 'recordatorio', 'fecha_importante'])],
            'start_at' => [$requiredRule, 'string', $this->eventDateRule()],
            'end_at' => ['sometimes', 'nullable', 'string', $this->eventDateRule()],
            'color' => ['sometimes', 'nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'status' => ['sometimes', 'nullable', 'string', Rule::in(['pendiente', 'en_progreso', 'completada'])],
        ];
    }

    private function eventDateRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            if (! is_string($value) || ! $this->isAcceptedDateValue($value)) {
                $fail('El campo ' . $attribute . ' debe tener formato Y-m-d H:i:s o ISO 8601.');
            }
        };
    }

    private function isAcceptedDateValue(string $value): bool
    {
        return (bool) preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $value)
            || (bool) preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d+)?(?:Z|[+-]\d{2}:\d{2})$/', $value);
    }

    private function parseEventDate(string $value): Carbon
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $value) === 1) {
            return Carbon::createFromFormat('Y-m-d H:i:s', $value);
        }

        return Carbon::parse($value);
    }

    private function parseNullableEventDate(mixed $value): ?Carbon
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        return $this->parseEventDate($value);
    }

    private function resolveCompletedAt(string $type, ?string $status): ?Carbon
    {
        if ($type === 'tarea' && $status === 'completada') {
            return Carbon::now();
        }

        return null;
    }

    private function resolveAccessibleEvent(Request $request, string $id): Event|JsonResponse
    {
        $event = Event::query()->find($id);

        if (! $event) {
            return $this->errorResponse('Evento no encontrado.', null, 404);
        }

        if ((int) $event->user_id !== (int) $request->user()->id) {
            return $this->errorResponse('No tienes permiso para acceder a este evento.', null, 403);
        }

        return $event;
    }

    private function successResponse(mixed $data, string $message, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message,
        ], $status);
    }

    private function errorResponse(string $message, mixed $data = null, int $status = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'data' => $data,
            'message' => $message,
        ], $status);
    }

    private function validationErrorResponse(array $errors): JsonResponse
    {
        return $this->errorResponse('Error de validación.', [
            'errors' => $errors,
        ], 422);
    }
}