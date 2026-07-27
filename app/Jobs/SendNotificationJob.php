<?php

namespace App\Jobs;

use App\Contracts\NotificationServiceInterface;
use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Event $event)
    {
    }

    public function handle(NotificationServiceInterface $notificationService): void
    {
        $notificationService->sendAlert($this->event);
    }
}
