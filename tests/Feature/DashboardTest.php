<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-07-26 08:00:00'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_dashboard_lists_only_today_events_that_are_not_completed(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        Event::create([
            'user_id' => $user->id,
            'title' => 'Primero del día',
            'description' => null,
            'type' => 'recordatorio',
            'start_at' => '2026-07-26 09:00:00',
            'end_at' => null,
            'color' => '#111111',
            'status' => 'pendiente',
            'completed_at' => null,
        ]);

        Event::create([
            'user_id' => $user->id,
            'title' => 'Completado hoy',
            'description' => null,
            'type' => 'tarea',
            'start_at' => '2026-07-26 10:00:00',
            'end_at' => null,
            'color' => '#222222',
            'status' => 'completada',
            'completed_at' => Carbon::now(),
        ]);

        Event::create([
            'user_id' => $user->id,
            'title' => 'Mañana',
            'description' => null,
            'type' => 'tarea',
            'start_at' => '2026-07-27 08:00:00',
            'end_at' => null,
            'color' => '#333333',
            'status' => 'pendiente',
            'completed_at' => null,
        ]);

        Event::create([
            'user_id' => $user->id,
            'title' => 'Importante hoy',
            'description' => null,
            'type' => 'fecha_importante',
            'start_at' => '2026-07-26 11:00:00',
            'end_at' => null,
            'color' => '#444444',
            'status' => 'pendiente',
            'completed_at' => null,
        ]);

        $response = $this->getJson('/api/dashboard/today');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data.events')
            ->assertJsonPath('data.events.0.title', 'Primero del día');
    }

    public function test_completed_event_disappears_from_today_dashboard(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $event = Event::create([
            'user_id' => $user->id,
            'title' => 'Pendiente de completar',
            'description' => null,
            'type' => 'tarea',
            'start_at' => '2026-07-26 13:00:00',
            'end_at' => null,
            'color' => '#555555',
            'status' => 'pendiente',
            'completed_at' => null,
        ]);

        $this->getJson('/api/dashboard/today')
            ->assertOk()
            ->assertJsonCount(1, 'data.events');

        $statusResponse = $this->patchJson('/api/events/'.$event->id.'/status', [
            'status' => 'completada',
        ]);

        $statusResponse->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.event.status', 'completada');

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'status' => 'completada',
        ]);

        $this->assertNotNull(Event::find($event->id)?->completed_at);

        $this->getJson('/api/dashboard/today')
            ->assertOk()
            ->assertJsonCount(0, 'data.events');
    }
}
