<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RendersHiddenPages;
use App\Mail\AccidentReportNotification;
use App\Support\AccidentReports;
use App\Support\ReportHtml;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Entry;
use Symfony\Component\HttpFoundation\Response;

/**
 * Accident report (Unfallmeldung).
 *
 * /unfallmeldung?k=CODE  staff (link in the Infosoft app): vehicle, passenger and staff accidents
 * /unfall-fahrgast       passengers (QR code on the business card)
 * /b                     the other party types in the code from the driver's phone
 *
 * The report HTML is built in the browser, sanitized here, saved with its files and mailed.
 */
class AccidentReportController extends Controller
{
    use RendersHiddenPages;

    public function staff(Request $request): Response
    {
        $this->authorizeStaff($request);
        $code = (string) $request->query('k');

        return $this->page('staff', url('/unfallmeldung'), [
            'submitUrl' => url('/unfallmeldung') . '?k=' . urlencode($code),
            // QR code on the business card (shown when handing it over)
            'passengerUrl' => url('/unfall-fahrgast'),
            'otherParty' => [
                'startUrl' => url('/unfallmeldung/b') . '?k=' . urlencode($code),
                'statusUrl' => url('/unfallmeldung/b/__CODE__/status') . '?k=' . urlencode($code),
                'pageUrl' => url('/unfallmeldung/b/__CODE__'),
                'manualUrl' => preg_replace('#^https?://(www\.)?#', '', url('/b')),
            ],
        ]);
    }

    public function passenger(): Response
    {
        return $this->page('passenger', url('/unfall-fahrgast'), [
            'submitUrl' => url('/unfall-fahrgast'),
        ]);
    }

    public function storeStaff(Request $request): JsonResponse
    {
        $this->authorizeStaff($request);

        return $this->store($request, AccidentReports::STAFF_TYPES);
    }

    public function storePassenger(Request $request): JsonResponse
    {
        return $this->store($request, AccidentReports::PASSENGER_TYPES);
    }

    /** /b: the other party types in the 6-character code */
    public function codeEntry(Request $request): Response
    {
        $code = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $request->query('code', '')));

        if (strlen($code) === 6) {
            return redirect('/unfallmeldung/b/' . $code);
        }

        return $this->hiddenPage('accident-report-code', 'Unfallmeldung', url('/b'), variables: [
            'code_missing' => $request->has('code'),
        ]);
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function page(string $mode, string $canonical, array $config): Response
    {
        return $this->hiddenPage('accident-report', 'Unfallmeldung', $canonical, [
            'mode' => $mode,
            'vehicles' => AccidentReports::vehicles(),
            'lines' => AccidentReports::lines(),
            'recipients' => [
                'ate' => AccidentReports::recipient('vehicle'),
                'staff' => AccidentReports::recipient('staff'),
            ],
            ...$config,
        ]);
    }

    /**
     * @param  list<string>  $types  report types allowed from this page
     */
    private function store(Request $request, array $types): JsonResponse
    {
        // Honeypot: drop bots silently
        if (filled($request->input('website'))) {
            return $this->hidden(response()->json(['success' => true, 'mail_sent' => true]));
        }

        $maxKb = (int) config('accident_reports.max_file_kb');
        $data = $request->validate([
            'type' => ['required', Rule::in($types)],
            'subject' => 'required|string|max:300',
            'copy_to' => 'nullable|email|max:200',
            'report' => 'required|string|max:4000000',
            'other_party_code' => 'nullable|alpha_num|size:6',
            'files' => 'nullable|array|max:' . (int) config('accident_reports.max_files'),
            'files.*' => "file|mimes:jpg,jpeg,png|max:$maxKb",
        ], [
            ...AccidentReports::VALIDATION_MESSAGES,
            'copy_to.email' => 'Die E-Mail-Adresse für die Kopie ist ungültig.',
            'files.max' => 'Zu viele Fotos.',
            'files.*.max' => 'Ein Foto ist zu gross.',
            'files.*.mimes' => 'Bitte nur Fotos (JPG oder PNG) hochladen.',
            'files.*.file' => 'Ein Foto konnte nicht hochgeladen werden.',
        ]);

        AccidentReports::ensureSetup();

        $type = $data['type'];
        $subject = trim(preg_replace('/\s+/', ' ', $data['subject']));
        $id = now()->format('Y-m-d_His') . "_{$type}_" . Str::lower(Str::random(5));
        $folder = now()->format('Y/m') . "/$id";

        $files = $this->storeUploads($request->file('files', []), $folder);
        if (! empty($data['other_party_code'])) {
            $files = [...$files, ...$this->takeOverOtherPartyPhotos($data['other_party_code'], $folder, array_column($files, 'name'))];
        }

        $report = ReportHtml::sanitize($data['report']);
        $reportPath = "$folder/Unfallbericht.html";
        Storage::disk(AccidentReports::DISK)->put($reportPath, ReportHtml::document($subject, $report));
        $this->registerAsset($reportPath);

        $recipient = AccidentReports::recipient($type);
        $copyTo = $data['copy_to'] ?? null;
        if ($copyTo && strcasecmp($copyTo, $recipient) === 0) {
            $copyTo = null;
        }

        $entry = Entry::make()
            ->collection(AccidentReports::COLLECTION)
            ->slug(Str::slug($id))
            ->data([
                'title' => $subject,
                // ISO format so the date field sorts correctly in the CP
                'submitted_at' => now()->format('Y-m-d H:i:s'),
                'type' => AccidentReports::TYPES[$type],
                'recipient' => $recipient,
                'copy_to' => $copyTo,
                'files' => [...array_column($files, 'path'), $reportPath],
                'report' => Str::limit(ReportHtml::toText($report), 20000),
                'mail_sent' => false,
            ]);
        $entry->save();

        // The report is saved. If mailing fails, it shows in the CP with «Mail versendet: aus».
        try {
            Mail::to($recipient)->send(new AccidentReportNotification($subject, $report, $files, $copyTo, false));
            $entry->set('mail_sent', true)->save();
        } catch (\Throwable $e) {
            Log::error('Unfallmeldung: mail to ATE failed', ['entry' => $entry->id(), 'error' => $e->getMessage()]);

            return $this->hidden(response()->json(['success' => true, 'mail_sent' => false]));
        }

        if ($copyTo) {
            try {
                Mail::to($copyTo)->send(new AccidentReportNotification($subject, $report, $files, config('accident_reports.reply_to'), true));
            } catch (\Throwable $e) {
                Log::warning('Unfallmeldung: copy mail failed', ['entry' => $entry->id(), 'error' => $e->getMessage()]);
            }
        }

        return $this->hidden(response()->json(['success' => true, 'mail_sent' => true]));
    }

    /**
     * Save uploaded photos under the name the form gave them (e.g. «Führerausweis ATE vorne.jpg»).
     *
     * @param  array<int, UploadedFile>  $uploads
     * @return list<array{disk: string, path: string, name: string, mime: string}>
     */
    private function storeUploads(array $uploads, string $folder): array
    {
        $files = [];

        foreach ($uploads as $upload) {
            $name = self::uniqueFileName($upload->getClientOriginalName(), array_column($files, 'name'));
            Storage::disk(AccidentReports::DISK)->putFileAs($folder, $upload, $name);
            $files[] = $this->registerAsset("$folder/$name", $upload->getMimeType());
        }

        return $files;
    }

    /**
     * Photos the other party sent from their own phone (QR flow) move into the report folder.
     *
     * @param  list<string>  $taken  file names already used in the folder
     * @return list<array{disk: string, path: string, name: string, mime: string}>
     */
    private function takeOverOtherPartyPhotos(string $code, string $folder, array $taken): array
    {
        $session = AccidentReports::otherParty($code);
        if (! $session || empty($session['done'])) {
            return [];
        }

        $disk = Storage::disk(AccidentReports::DISK);
        $files = [];

        foreach ($session['files'] ?? [] as $path => $name) {
            if (! $disk->exists($path)) {
                continue;
            }
            $name = self::uniqueFileName($name, [...$taken, ...array_column($files, 'name')]);
            $disk->move($path, "$folder/$name");
            $files[] = $this->registerAsset("$folder/$name", $disk->mimeType("$folder/$name") ?: 'image/jpeg');
        }

        $disk->deleteDirectory(AccidentReports::OTHER_PARTY_FOLDER . '/' . strtoupper($code));
        cache()->forget(AccidentReports::otherPartyCacheKey($code));

        return $files;
    }

    /** @return array{disk: string, path: string, name: string, mime: string} */
    private function registerAsset(string $path, string $mime = 'text/html'): array
    {
        AssetContainer::findByHandle(AccidentReports::CONTAINER)->makeAsset($path)->save();

        return ['disk' => AccidentReports::DISK, 'path' => $path, 'name' => basename($path), 'mime' => $mime];
    }

    /** Keep letters (incl. umlauts), digits and a few separators; add « (2)» on duplicates. */
    private static function uniqueFileName(string $name, array $taken): string
    {
        $name = preg_replace('/[^\p{L}\p{N} ._()\-]/u', '', str_replace(['/', '\\'], '-', $name));
        $base = trim(pathinfo($name, PATHINFO_FILENAME), ' .') ?: 'Foto';
        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $extension = in_array($extension, ['jpg', 'jpeg', 'png'], true) ? $extension : 'jpg';

        $candidate = "$base.$extension";
        for ($i = 2; in_array($candidate, $taken, true); $i++) {
            $candidate = "$base ($i).$extension";
        }

        return $candidate;
    }

    private function authorizeStaff(Request $request): void
    {
        abort_unless(AccidentReports::staffCodeIsValid($request->query('k', $request->input('k'))), 404);
    }
}
