<?php

namespace App\Console\Commands;

use App\Support\AccidentReports;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Entry;

/**
 * Data protection: delete stored accident reports (entry, photos, ID cards, report file)
 * after the retention period. The mails in the mailboxes are not affected.
 *
 *   php artisan accident-reports:prune
 *   php artisan accident-reports:prune --dry-run
 */
class PruneAccidentReports extends Command
{
    protected $signature = 'accident-reports:prune {--dry-run : Only list what would be deleted}';

    protected $description = 'Delete stored accident reports after the retention period';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $limit = now()->subDays((int) config('accident_reports.retention_days'));
        $deleted = 0;
        $container = AssetContainer::findByHandle(AccidentReports::CONTAINER);
        $disk = Storage::disk(AccidentReports::DISK);
        $folders = [];

        $entries = Entry::query()->where('collection', AccidentReports::COLLECTION)->get()
            ->filter(fn ($entry) => Carbon::parse($entry->get('submitted_at') ?? $entry->lastModified())->lt($limit));

        foreach ($entries as $entry) {
            $this->line(($dryRun ? '[dry run] ' : '') . 'delete ' . $entry->get('title'));
            if (! $dryRun) {
                foreach ((array) $entry->get('files') as $path) {
                    $container?->asset($path)?->delete();
                    $folders[] = dirname($path);
                }
                $entry->delete();
            }
            $deleted++;
        }

        // Report folders are empty now
        foreach (array_unique($folders) as $folder) {
            if ($folder !== '.' && ! $disk->allFiles($folder)) {
                $disk->deleteDirectory($folder);
            }
        }

        // Other-party photos of a QR session the driver never sent (cache expires after 12 h)
        foreach ($disk->directories(AccidentReports::OTHER_PARTY_FOLDER) as $folder) {
            $newest = collect($disk->allFiles($folder))->map(fn ($file) => $disk->lastModified($file))->max() ?? 0;
            if ($newest < now()->subDays(2)->getTimestamp()) {
                $this->line(($dryRun ? '[dry run] ' : '') . 'delete ' . $folder);
                $dryRun || $disk->deleteDirectory($folder);
                $deleted++;
            }
        }

        $this->info($deleted . ($dryRun ? ' would be deleted.' : ' deleted.'));

        return self::SUCCESS;
    }
}
