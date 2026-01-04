<?php

namespace App\Mail;

use App\Models\Demandante;
use App\Models\Oferta;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NuevaInscripcionMail extends Mailable
{
    use Queueable, SerializesModels;

    public $oferta;
    public $demandante;

    /**
     * Create a new message instance.
     */
    public function __construct(Oferta $oferta, Demandante $demandante)
    {
        $this->oferta = $oferta;
        $this->demandante = $demandante;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nueva Inscripción en tu Oferta: ' . $this->oferta->nombre,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.nueva_inscripcion',
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
