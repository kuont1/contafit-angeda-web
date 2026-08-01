<?php

namespace App\Mail;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EventReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public Event $event;

    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Recordatorio de Evento: '.$this->event->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 8px;'>
                <h2 style='color: #4f46e5;'>Recordatorio de Contafit Agenda</h2>
                <p>Hola <strong>{$this->event->user->name}</strong>,</p>
                <p>Tienes un evento programado próximamente en tu agenda:</p>
                <div style='background-color: #f9fafb; padding: 15px; border-left: 4px solid {$this->event->color}; border-radius: 4px; margin: 15px 0;'>
                    <h3 style='margin-top: 0;'>{$this->event->title}</h3>
                    <p><strong>Tipo:</strong> ".ucfirst($this->event->type)."</p>
                    <p><strong>Fecha / Hora:</strong> {$this->event->start_at}</p>
                    ".($this->event->description ? "<p><strong>Descripción:</strong> {$this->event->description}</p>" : '')."
                </div>
                <p style='color: #6b7280; font-size: 0.9em;'>Este es un mensaje automático enviado desde tu agenda personal Contafit.</p>
            </div>
            "
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
