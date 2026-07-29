<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ProcessNotifications extends Command
{
    protected $signature = 'notifications:process-pending';
    protected $description = 'Procesar e intentar enviar notificaciones pendientes programadas';

    public function handle(NotificationService $notificationService): void
    {
        $pending = Notification::where('status', 'pendiente')
            ->where('scheduled_at', '<=', Carbon::now())
            ->with('event')
            ->get();

        $this->info("Procesando " . $pending->count() . " notificaciones pendientes...");

        foreach ($pending as $notification) {
            if ($notification->event) {
                $success = $notificationService->sendAlert($notification->event);
                if ($success) {
                    $this->info("Notificación para el evento ID {$notification->event_id} enviada.");
                } else {
                    $this->warn("Falló el envío de la notificación para el evento ID {$notification->event_id}.");
                }
            }
        }
    }
}
