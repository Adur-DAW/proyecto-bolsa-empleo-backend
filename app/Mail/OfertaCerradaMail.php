<?php

namespace App\Mail;

use App\Models\Oferta;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OfertaCerradaMail extends Mailable
{
    use Queueable, SerializesModels;

    public $oferta;
    public $motivo;

    /**
     * Create a new message instance.
     * @param string $motivo 'adjudicada' o 'cerrada'
     */
    public function __construct(Oferta $oferta, string $motivo = 'cerrada')
    {
        $this->oferta = $oferta;
        $this->motivo = $motivo;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = 'Actualización sobre la Oferta: ' . $this->oferta->nombre;
        
        if ($this->motivo === 'invitacion') {
            $subject = 'Te han enviado una Oferta de Empleo: ' . $this->oferta->nombre;
        }

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.oferta_cerrada',
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
