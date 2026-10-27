<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClientPasswordResetCode extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $clientName,
        public string $code,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reset your Smart Holidays password',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.client-password-reset-code',
            text: 'emails.client-password-reset-code-text',
        );
    }
}
