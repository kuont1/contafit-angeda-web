<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_event(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $payload = [
            'title' => 'Reunión de seguimiento',
            'description' => 'Revisar avances del proyecto',
            'type' => 'tarea',
            'start_at' => '2026-07-26 09:00:00',
            'end_at' => '2026-07-26 10:00:00',
            'color' => '#FFAA00',
            'status' => 'pendiente',
        ];

        $response = $this->postJson('/api/events', $payload);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.event.title', 'Reunión de seguimiento')
            ->assertJsonPath('data.event.user_id', $user->id);

        $this->assertDatabaseHas('events', [
            'user_id' => $user->id,
            'title' => 'Reunión de seguimiento',
            'type' => 'tarea',
            'status' => 'pendiente',
        ]);
    }

    public function test_user_cannot_view_or_modify_another_users_event(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $event = Event::create([
            'user_id' => $owner->id,
            'title' => 'Evento privado',
            'description' => 'Solo dueño',
            'type' => 'recordatorio',
            'start_at' => '2026-07-26 12:00:00',
            'end_at' => null,
            'color' => '#00AAFF',
            'status' => 'pendiente',
            'completed_at' => null,
        ]);

        $this->actingAs($intruder, 'sanctum');

        $showResponse = $this->getJson('/api/events/'.$event->id);
        $showResponse->assertStatus(403)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'No tienes permiso para acceder a este evento.');

        $updateResponse = $this->patchJson('/api/events/'.$event->id, [
            'title' => 'Intento de edición',
        ]);

        $updateResponse->assertStatus(403)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'No tienes permiso para acceder a este evento.');
    }

    public function test_user_can_soft_delete_event(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $event = Event::create([
            'user_id' => $user->id,
            'title' => 'Evento a eliminar',
            'description' => null,
            'type' => 'recordatorio',
            'start_at' => '2026-07-26 15:00:00',
            'end_at' => null,
            'color' => '#112233',
            'status' => 'pendiente',
            'completed_at' => null,
        ]);

        $response = $this->deleteJson('/api/events/'.$event->id);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Evento eliminado correctamente.');

        $this->assertSoftDeleted('events', [
            'id' => $event->id,
        ]);
    }

    // --- RF-06: Eventos Recurrentes e Instancias ---

    public function test_recurring_event_requires_recurrence_frequency(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/events', [
            'title' => 'Reunión semanal de equipo',
            'type' => 'tarea',
            'start_at' => '2026-08-01 09:00:00',
            'is_recurring' => true,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_user_can_create_recurring_event(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/events', [
            'title' => 'Reunión semanal de equipo',
            'type' => 'tarea',
            'start_at' => '2026-08-01 09:00:00',
            'is_recurring' => true,
            'recurrence_frequency' => 'semanal',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.event.is_recurring', true)
            ->assertJsonPath('data.event.recurrence_frequency', 'semanal');
    }

    public function test_user_can_update_event_instance(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $parent = Event::create([
            'user_id' => $user->id,
            'title' => 'Clase de Yoga',
            'type' => 'recordatorio',
            'start_at' => '2026-08-01 07:00:00',
            'is_recurring' => true,
            'recurrence_frequency' => 'semanal',
        ]);

        $response = $this->patchJson("/api/events/{$parent->id}/instance", [
            'title' => 'Clase de Yoga Especial',
            'start_at' => '2026-08-08 08:00:00',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.event.recurrence_parent_id', $parent->id)
            ->assertJsonPath('data.event.title', 'Clase de Yoga Especial');

        $this->assertDatabaseHas('events', [
            'recurrence_parent_id' => $parent->id,
            'title' => 'Clase de Yoga Especial',
        ]);
    }

    public function test_user_can_soft_delete_event_instance_without_affecting_parent(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $parent = Event::create([
            'user_id' => $user->id,
            'title' => 'Entrenamiento diario',
            'type' => 'tarea',
            'start_at' => '2026-08-01 06:00:00',
            'is_recurring' => true,
            'recurrence_frequency' => 'diaria',
        ]);

        $instance = Event::create([
            'user_id' => $user->id,
            'title' => 'Entrenamiento diario (Modificado)',
            'type' => 'tarea',
            'start_at' => '2026-08-02 06:00:00',
            'is_recurring' => false,
            'recurrence_parent_id' => $parent->id,
        ]);

        $response = $this->deleteJson("/api/events/{$instance->id}/instance");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Instancia del evento eliminada correctamente.');

        $this->assertSoftDeleted('events', ['id' => $instance->id]);
        $this->assertDatabaseHas('events', ['id' => $parent->id, 'deleted_at' => null]);
    }

    // --- RF-07: Búsqueda y Filtros Avanzados ---

    public function test_user_can_filter_events_with_multiple_parameters(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        Event::create([
            'user_id' => $user->id,
            'title' => 'Reunión de planificación',
            'description' => 'Revisar metas trimestrales',
            'type' => 'tarea',
            'status' => 'pendiente',
            'start_at' => '2026-08-10 10:00:00',
        ]);

        Event::create([
            'user_id' => $user->id,
            'title' => 'Reunión de seguimiento',
            'description' => 'Chequear avances',
            'type' => 'recordatorio',
            'status' => 'en_progreso',
            'start_at' => '2026-08-15 11:00:00',
        ]);

        Event::create([
            'user_id' => $user->id,
            'title' => 'Cumpleaños de Director',
            'description' => 'Celebración',
            'type' => 'fecha_importante',
            'status' => 'pendiente',
            'start_at' => '2026-08-20 12:00:00',
        ]);

        // Filtrar por palabra clave search=Reunión, type=tarea, status=pendiente
        $response = $this->getJson('/api/events?search=Reunión&type=tarea&status=pendiente');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data.events')
            ->assertJsonPath('data.events.0.title', 'Reunión de planificación');

        // Filtrar por rango de fechas start_date y end_date
        $rangeResponse = $this->getJson('/api/events?start_date=2026-08-14&end_date=2026-08-22');

        $rangeResponse->assertOk()
            ->assertJsonCount(2, 'data.events');
    }
}
