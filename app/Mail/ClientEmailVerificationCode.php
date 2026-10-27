<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClientEmailVerificationCode extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $clientName,
        public string $code,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Smart Holidays verification code',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.client-email-verification-code',
            text: 'emails.client-email-verification-code-text',
        );
    }
}
