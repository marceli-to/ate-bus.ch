<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Fundmeldung an das Fundpostfach.
 *
 * ACHTUNG: Betreff, Tabellenzeilen und Feldnamen werden beim Kunden automatisch
 * ausgewertet. Aufbau 1:1 aus der gelieferten fundmeldung.php – nicht ändern.
 */
class FoundItemNotification extends Mailable
{
    use Queueable, SerializesModels;

    /** Reihenfolge und Namen der Tabellenzeilen (aus fundmeldung.php) */
    public const ROWS = ['Gefunden von', 'Name', 'Datum', 'Uhrzeit', 'Buslinie', 'Bus-Nr', 'Fundort im Bus', 'Gegenstand', 'Beschreibung', 'Abgegeben bei'];

    /**
     * @param  array<string, string>  $report  Werte nach Zeilenname (ROWS), Datum bereits als d.m.Y
     * @param  list<array{disk: string, path: string, name: string, mime: string}>  $photos
     */
    public function __construct(
        public string $role,
        public array $report,
        public string $subjectLine,
        public array $photos = [],
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), 'ATE Bus Fundmeldung'),
            subject: $this->subjectLine,
        );
    }

    public function content(): Content
    {
        return new Content(htmlString: $this->body());
    }

    public function attachments(): array
    {
        return array_map(
            fn ($photo) => Attachment::fromStorageDisk($photo['disk'], $photo['path'])->as($photo['name'])->withMime($photo['mime']),
            $this->photos,
        );
    }

    private function body(): string
    {
        $h = fn ($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');

        $rows = '';
        foreach (self::ROWS as $key) {
            $rows .= '<tr><td style="padding:6px 12px;color:#555">' . $h($key) . '</td><td style="padding:6px 12px">' . nl2br($h($this->report[$key] ?? '')) . '</td></tr>';
        }

        return '<h2>Neue Fundmeldung (' . $h($this->role) . ')</h2><p>Ein Fundgegenstand wurde erfasst:</p>'
            . '<table style="border-collapse:collapse">' . $rows . '</table>'
            . '<p>Fotos: ' . count($this->photos) . ' im Anhang</p>';
    }
}
