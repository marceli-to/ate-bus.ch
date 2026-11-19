<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
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
            subject: "Gesucht – Neue Verlustmeldung: {$this->reportData['firstname']} {$this->reportData['lastname']}",
            // «Antworten» in Outlook geht direkt an die Person, Absender bleibt noreply
            replyTo: [new Address($this->reportData['email'], trim("{$this->reportData['firstname']} {$this->reportData['lastname']}"))],
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
