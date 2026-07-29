<?php

namespace Tests\Feature;

use App\Jobs\SendNotificationJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class NotificationJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_dispatches_send_notification_job_when_event_is_created(): void
    {
        Queue::fake();

        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/events', [
            'title' => 'Conferencia Importante',
            'type' => 'recordatorio',
            'start_at' => now()->addDay()->toDateTimeString(),
            'end_at' => now()->addDay()->addHour()->toDateTimeString(),
            'color' => '#10B981',
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('success', true);

        // Verificar que el Job de notificación asíncrona se haya encolado
        Queue::assertPushed(SendNotificationJob::class, function ($job) {
            return $job->event->title === 'Conferencia Importante';
        });
    }
}
