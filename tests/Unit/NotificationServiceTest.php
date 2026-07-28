<?php

namespace Tests\Unit;

use App\Models\Event;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculates_exact_notification_advance_time(): void
    {
        $user = User::factory()->create();

        // Evento programado para dentro de 2 horas
        $startAt = Carbon::now()->addHours(2);
        $event = Event::create([
            'user_id' => $user->id,
            'title' => 'Reunión de prueba',
            'type' => 'tarea',
            'start_at' => $startAt,
            'status' => 'pendiente',
        ]);

        $service = new NotificationService;

        // Calcular hora con 15 minutos de anticipación
        $scheduledTime = $service->calculateScheduledTime($event, 15);

        // La hora programada debe ser exactamente start_at menos 15 minutos
        $expectedTime = $startAt->copy()->subMinutes(15);

        $this->assertEquals(
            $expectedTime->toDateTimeString(),
            $scheduledTime->toDateTimeString()
        );
    }
}
