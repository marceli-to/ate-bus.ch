<?php

namespace App\Mail;

use App\Support\ReportHtml;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Accident report, to ATE (info@ / personal@) or as a copy to the person involved.
 * Body = the report built in the browser (sanitized), photos as attachments, plus «Unfallbericht.html» for printing.
 */
class AccidentReportNotification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  list<array{disk: string, path: string, name: string, mime: string}>  $files
     */
    public function __construct(
        public string $subjectLine,
        public string $report,
        public array $files,
        public ?string $replyToAddress,
        public bool $isCopy,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), 'Unfallmeldung ATE Bus AG'),
            replyTo: $this->replyToAddress ? [new Address($this->replyToAddress)] : [],
            subject: ($this->isCopy ? 'Kopie: ' : '') . $this->subjectLine,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.accident-report');
    }

    public function attachments(): array
    {
        $attachments = array_map(
            fn ($file) => Attachment::fromStorageDisk($file['disk'], $file['path'])->as($file['name'])->withMime($file['mime']),
            $this->files,
        );

        $attachments[] = Attachment::fromData(fn () => ReportHtml::document($this->subjectLine, $this->report), 'Unfallbericht.html')
            ->withMime('text/html');

        return $attachments;
    }
}
