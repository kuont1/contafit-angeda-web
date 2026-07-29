<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class TrashTest extends TestCase
{
    use RefreshDatabase;

    public function test_soft_deleted_events_appear_in_trash_endpoints(): void
    {
        $user = User::factory()->create();
        $event = Event::create([
            'user_id' => $user->id,
            'title' => 'Evento a papelera',
            'type' => 'tarea',
            'start_at' => now()->toDateTimeString(),
        ]);

        $event->delete();

        // Probar endpoint /api/events/trash
        $res1 = $this->actingAs($user)->getJson('/api/events/trash');
        $res1->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data.events')
            ->assertJsonPath('data.events.0.id', $event->id);

        // Probar endpoint alias /api/trash
        $res2 = $this->actingAs($user)->getJson('/api/trash');
        $res2->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data.events');
    }

    public function test_user_can_restore_trashed_event(): void
    {
        $user = User::factory()->create();
        $event = Event::create([
            'user_id' => $user->id,
            'title' => 'Evento a restaurar',
            'type' => 'tarea',
            'start_at' => now()->toDateTimeString(),
        ]);
        $event->delete();

        $response = $this->actingAs($user)
            ->postJson("/api/events/{$event->id}/restore");

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'deleted_at' => null,
        ]);
    }

    public function test_user_can_force_delete_trashed_event(): void
    {
        $user = User::factory()->create();
        $event = Event::create([
            'user_id' => $user->id,
            'title' => 'Evento borrado definitivo',
            'type' => 'tarea',
            'start_at' => now()->toDateTimeString(),
        ]);
        $event->delete();

        $response = $this->actingAs($user)
            ->deleteJson("/api/events/{$event->id}/force-delete");

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('events', [
            'id' => $event->id,
        ]);
    }

    public function test_purge_command_removes_events_older_than_30_days(): void
    {
        $user = User::factory()->create();

        // Evento borrado hace 35 días (debe purgarse)
        $oldEvent = Event::create([
            'user_id' => $user->id,
            'title' => 'Evento viejo en papelera',
            'type' => 'tarea',
            'start_at' => now()->subDays(40)->toDateTimeString(),
        ]);
        $oldEvent->delete();
        Event::withTrashed()->where('id', $oldEvent->id)->update(['deleted_at' => now()->subDays(35)]);

        // Evento borrado hace 10 días (debe conservarse)
        $recentEvent = Event::create([
            'user_id' => $user->id,
            'title' => 'Evento reciente en papelera',
            'type' => 'tarea',
            'start_at' => now()->subDays(12)->toDateTimeString(),
        ]);
        $recentEvent->delete();
        Event::withTrashed()->where('id', $recentEvent->id)->update(['deleted_at' => now()->subDays(10)]);

        // Ejecutar comando de purga automática
        Artisan::call('events:purge-trash');

        // El viejo debe ser eliminado definitivamente de la BD
        $this->assertDatabaseMissing('events', ['id' => $oldEvent->id]);

        // El reciente debe continuar en la papelera (soft deleted)
        $this->assertSoftDeleted('events', ['id' => $recentEvent->id]);
    }
}
