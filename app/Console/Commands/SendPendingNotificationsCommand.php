<?php

namespace App\Console\Commands;

use App\Mail\EventReminderMail;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPendingNotificationsCommand extends Command
{
    protected $signature = 'notifications:send';
    protected $description = 'Procesa y envía las notificaciones por correo electrónico pendientes vía Brevo/SMTP';

    public function handle(): int
    {
        $now = Carbon::now();
        $this->info("Buscando notificaciones pendientes hasta: {$now->toDateTimeString()}");

        $pendingNotifications = Notification::with(['event.user'])
            ->where('status', 'pendiente')
            ->where('scheduled_at', '<=', $now)
            ->get();

        if ($pendingNotifications->isEmpty()) {
            $this->info("No hay notificaciones pendientes por enviar.");
            return Command::SUCCESS;
        }

        $sentCount = 0;
        $failedCount = 0;

        foreach ($pendingNotifications as $notification) {
            $event = $notification->event;

            if (! $event || ! $event->user || ! $event->user->email) {
                $notification->update(['status' => 'fallida']);
                $failedCount++;
                continue;
            }

            try {
                Mail::to($event->user->email)->send(new EventReminderMail($event));

                $notification->update([
                    'status' => 'enviada',
                    'sent_at' => Carbon::now(),
                ]);

                $this->info("✔ Notificación enviada a {$event->user->email} para el evento: {$event->title}");
                $sentCount++;
            } catch (\Throwable $e) {
                Log::error("Error al enviar notificación ID {$notification->id}: " . $e->getMessage());
                $notification->update(['status' => 'fallida']);
                $failedCount++;
                $this->error("✖ Error al enviar a {$event->user->email}: " . $e->getMessage());
            }
        }

        $this->info("Proceso completado. Enviadas: {$sentCount}, Fallidas: {$failedCount}");
        return Command::SUCCESS;
    }
}
