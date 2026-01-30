<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicationNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $applicationData
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Neue Bewerbung: {$this->applicationData['firstname']} {$this->applicationData['lastname']} - {$this->applicationData['job_title']}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.application-notification',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
