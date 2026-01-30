<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LostAndFoundConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $reportData
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Ihre Verlustmeldung bei ATE Bus',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.lost-and-found-confirmation',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
