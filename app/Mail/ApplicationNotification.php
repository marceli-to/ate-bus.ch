<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicationNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $applicationData,
        private array $files = [],
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Neue Bewerbung: {$this->applicationData['firstname']} {$this->applicationData['lastname']} - {$this->applicationData['job_title']}",
            // «Antworten» in Outlook geht direkt an die Person, Absender bleibt noreply
            replyTo: [new Address($this->applicationData['email'], trim("{$this->applicationData['firstname']} {$this->applicationData['lastname']}"))],
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
        return collect($this->files)
            ->filter(fn ($file) => file_exists($file['path']))
            ->map(fn ($file) => Attachment::fromPath($file['path'])->as($file['name']))
            ->values()
            ->all();
    }
}
