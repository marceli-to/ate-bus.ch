<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LostAndFoundNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $reportData
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Neue Verlustmeldung: {$this->reportData['firstname']} {$this->reportData['lastname']}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.lost-and-found-notification',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
