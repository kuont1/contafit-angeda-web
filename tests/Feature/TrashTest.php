<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrashTest extends TestCase
{
    use RefreshDatabase;

    public function test_soft_deleted_events_appear_in_trash(): void
    {
        $user = User::factory()->create();
        $event = Event::create([
            'user_id' => $user->id,
            'title' => 'Evento a borrar',
            'type' => 'tarea',
            'start_at' => now()->toDateTimeString(),
        ]);

        $event->delete();

        $response = $this->actingAs($user)
            ->getJson('/api/trash');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data.events')
            ->assertJsonPath('data.events.0.id', $event->id);
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
            ->postJson("/api/trash/{$event->id}/restore");

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
            ->deleteJson("/api/trash/{$event->id}/force");

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('events', [
            'id' => $event->id,
        ]);
    }
}
