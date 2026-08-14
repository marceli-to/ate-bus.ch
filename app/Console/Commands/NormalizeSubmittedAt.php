<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Statamic\Facades\Entry;

/**
 * "submitted_at" wurde ursprünglich als Text im Format d.m.Y bzw. d.m.Y H:i
 * gespeichert. Als Text sortiert das CP alphabetisch, also chronologisch falsch.
 * Dieser Befehl schreibt die bestehenden Werte ins ISO-Format um, damit das
 * Datumsfeld korrekt sortiert.
 */
class NormalizeSubmittedAt extends Command
{
    protected $signature = 'entries:normalize-submitted-at
                            {--collection=* : Nur diese Collections (Default: lost_and_found, applications)}
                            {--dry-run : Nur anzeigen, nichts speichern}';

    protected $description = 'Wandelt das Feld submitted_at bestehender Einträge ins ISO-Format um';

    public function handle(): int
    {
        $collections = $this->option('collection') ?: ['lost_and_found', 'applications'];
        $dryRun = $this->option('dry-run');

        foreach ($collections as $collection) {
            $this->info("Collection: {$collection}");

            $updated = 0;
            $skipped = 0;
            $failed = 0;

            foreach (Entry::query()->where('collection', $collection)->get() as $entry) {
                $current = (string) $entry->get('submitted_at');

                if ($current !== '' && preg_match('/^\d{4}-\d{2}-\d{2}/', $current)) {
                    $skipped++;

                    continue;
                }

                $date = $this->dateFromSlug($entry->slug())
                    ?? $this->dateFromValue($current)
                    ?? $this->dateFromFile($entry);

                if (! $date) {
                    $this->warn("  kein Datum ermittelbar: {$entry->slug()}");
                    $failed++;

                    continue;
                }

                $this->line(sprintf('  %-22s %s → %s', $entry->slug(), $current ?: '–', $date->format('Y-m-d H:i:s')));

                if (! $dryRun) {
                    $entry->set('submitted_at', $date->format('Y-m-d H:i:s'))->save();
                }

                $updated++;
            }

            $this->info("  → {$updated} umgestellt, {$skipped} bereits im ISO-Format, {$failed} ohne Datum");
        }

        if ($dryRun) {
            $this->comment('Dry-Run: es wurde nichts gespeichert.');
        }

        return self::SUCCESS;
    }

    /**
     * Die Slugs enthalten den Einreiche-Zeitpunkt: vorname-nachname-2026-08-13-073406
     */
    private function dateFromSlug(string $slug): ?Carbon
    {
        if (! preg_match('/-(\d{4})-(\d{2})-(\d{2})-(\d{2})(\d{2})(\d{2})$/', $slug, $m)) {
            return null;
        }

        return $this->make("{$m[1]}-{$m[2]}-{$m[3]} {$m[4]}:{$m[5]}:{$m[6]}");
    }

    private function dateFromValue(string $value): ?Carbon
    {
        foreach (['d.m.Y H:i:s', 'd.m.Y H:i', 'd.m.Y'] as $format) {
            try {
                $date = Carbon::createFromFormat($format, $value);
            } catch (\Throwable) {
                continue;
            }

            if ($date && $date->format($format) === $value) {
                return $date;
            }
        }

        return null;
    }

    private function dateFromFile($entry): ?Carbon
    {
        $path = $entry->path();

        return $path && file_exists($path) ? Carbon::createFromTimestamp(filemtime($path)) : null;
    }

    private function make(string $value): ?Carbon
    {
        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }
}
