<?php

namespace App\Services;

use App\Contracts\NotificationServiceInterface;
use App\Models\Event;
use App\Models\Notification as NotificationModel;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService implements NotificationServiceInterface
{
    public function calculateScheduledTime(Event $event, int $advanceMinutes = 15): CarbonInterface
    {
        $startAt = Carbon::parse($event->start_at);
        $advance = $event->reminder_minutes_before ?? $advanceMinutes;

        return $startAt->copy()->subMinutes($advance);
    }

    public function sendAlert(Event $event): bool
    {
        try {
            $user = $event->user;

            if (! $user || ! $user->email) {
                Log::warning("No se pudo enviar notificación para el evento ID {$event->id}: usuario sin email.");

                return false;
            }

            $apiKey = config('services.brevo.key');
            $senderEmail = config('mail.from.address', 'contafitmach@gmail.com');
            $sentSuccess = false;

            // Enviar vía API REST de Brevo si la API Key está configurada
            if ($apiKey) {
                try {
                    $response = Http::withHeaders([
                        'api-key' => $apiKey,
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ])->post('https://api.brevo.com/v3/smtp/email', [
                        'sender' => ['name' => 'ContaFit Agenda', 'email' => $senderEmail],
                        'to' => [['email' => $user->email, 'name' => $user->first_name ?? $user->name]],
                        'subject' => "⏰ Recordatorio: {$event->title}",
                        'textContent' => "⏰ Recordatorio de Evento - ContaFit Agenda\n\nHola ".($user->first_name ?? $user->name).",\n\nEste es un recordatorio automático para tu evento '{$event->title}' programado para el {$event->start_at}.\n\nDescripción: ".($event->description ?? 'Sin descripción')."\n\nSaludos,\nEquipo ContaFit Agenda Web",
                        'htmlContent' => '<html><body>
                            <h2>⏰ Recordatorio de Evento - ContaFit Agenda</h2>
                            <p>Hola <strong>'.($user->first_name ?? $user->name)."</strong>,</p>
                            <p>Este es un recordatorio automático para tu evento <strong>{$event->title}</strong> programado para el <strong>{$event->start_at}</strong>.</p>
                            <p><strong>Descripción:</strong> ".($event->description ?? 'Sin descripción').'</p>
                            <br><p>Saludos,<br>Equipo ContaFit Agenda Web</p>
                        </body></html>',
                    ]);

                    if ($response->successful()) {
                        $sentSuccess = true;
                        Log::info("Notificación enviada a {$user->email} vía Brevo API v3 (Message ID: ".$response->json('messageId').')');
                    } else {
                        Log::warning('Error respuesta Brevo API: '.$response->body());
                    }
                } catch (\Throwable $brevoErr) {
                    Log::warning('Excepción al contactar Brevo API: '.$brevoErr->getMessage());
                }
            }

            // Fallback a Mail Facade de Laravel si no se envió por la API REST
            if (! $sentSuccess) {
                try {
                    Mail::raw(
                        "Hola {$user->first_name},\n\nEste es un recordatorio automático para tu evento '{$event->title}' programado para el {$event->start_at}.\n\nDescripción: ".($event->description ?? 'Sin descripción')."\n\nSaludos,\nEquipo ContaFit Agenda",
                        function ($message) use ($user, $event) {
                            $message->to($user->email)
                                ->subject("⏰ Recordatorio: {$event->title}");
                        }
                    );
                } catch (\Throwable $mailErr) {
                    Log::warning("No se pudo entregar correo por Mail facade a {$user->email}: ".$mailErr->getMessage());
                }
            }

            // Registrar o actualizar el estado de la notificación a 'enviada' en BD
            NotificationModel::updateOrCreate(
                ['event_id' => $event->id, 'user_id' => $user->id],
                [
                    'scheduled_at' => $this->calculateScheduledTime($event),
                    'status' => 'enviada',
                    'sent_at' => Carbon::now(),
                ]
            );

            return true;
        } catch (\Throwable $e) {
            Log::error("Error al enviar alerta del evento ID {$event->id}: {$e->getMessage()}");

            return false;
        }
    }

    public function processPendingNotifications(): int
    {
        $pending = NotificationModel::where('status', 'pendiente')
            ->where('scheduled_at', '<=', Carbon::now())
            ->with('event.user')
            ->get();

        $processed = 0;
        foreach ($pending as $notification) {
            if ($notification->event && $notification->event->user) {
                if ($this->sendAlert($notification->event)) {
                    $processed++;
                }
            } else {
                $notification->update(['status' => 'fallida']);
            }
        }

        return $processed;
    }
}
