<?php

namespace Tests\Feature;

use App\Mail\EventReminderMail;
use App\Models\Event;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_send_pending_notifications_command_sends_emails(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $event = Event::create([
            'user_id' => $user->id,
            'title' => 'Reunión de prueba',
            'description' => 'Test mail',
            'type' => 'tarea',
            'start_at' => '2026-07-27 10:00:00',
            'color' => '#112233',
            'status' => 'pendiente',
        ]);

        $notification = Notification::create([
            'event_id' => $event->id,
            'scheduled_at' => Carbon::now()->subMinute(),
            'status' => 'pendiente',
        ]);

        $this->artisan('notifications:send')
            ->assertExitCode(0);

        Mail::assertSent(EventReminderMail::class, function ($mail) use ($user, $event) {
            return $mail->hasTo('test@example.com') &&
                   $mail->event->id === $event->id;
        });

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'status' => 'enviada',
        ]);
    }
}
