<?php

namespace App\Contracts;

use App\Models\Event;
use Carbon\CarbonInterface;

interface NotificationServiceInterface
{
    /**
     * Calcula la hora exacta de envío con la anticipación requerida (por defecto 15 minutos).
     */
    public function calculateScheduledTime(Event $event, int $advanceMinutes = 15): CarbonInterface;

    /**
     * Procesa el envío asíncrono de la alerta asociada a un evento.
     */
    public function sendAlert(Event $event): bool;

    /**
     * Procesa e intenta enviar todas las notificaciones pendientes que hayan cumplido su horario.
     */
    public function processPendingNotifications(): int;
}
