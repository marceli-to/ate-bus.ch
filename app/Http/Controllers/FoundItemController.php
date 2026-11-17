<?php

namespace App\Http\Controllers;

use App\Mail\FoundItemNotification;
use App\Support\FoundItems;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Entry;
use Statamic\View\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Fundmeldung für das Personal (Fahrer, Reinigung).
 * Nur über den Link aus der Infosoft-App erreichbar: /fundmeldung?k=CODE
 */
class FoundItemController extends Controller
{
    private const MAX_PHOTO_KB = 8 * 1024;

    private const PHOTO_TYPES = [
        'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/heic' => 'heic', 'image/heif' => 'heif',
    ];

    public function show(Request $request): Response
    {
        $this->authorizeCode($request);

        $config = [
            'code' => (string) $request->query('k'),
            'lines' => FoundItems::lines(),
            'items' => FoundItems::items(),
            'roles' => FoundItems::ROLES,
            'groups' => FoundItems::GROUPS,
            'dropOffs' => FoundItems::DROP_OFFS,
        ];

        $html = View::make('fundmeldung')
            ->layout('templates/layout-thin')
            ->with([
                'title' => 'Fundmeldung',
                'robots' => 'noindex_nofollow',
                'canonical_url' => url('/fundmeldung'),
                // Als JSON in <script type="application/json">: </script> & Co. maskieren
                'form_config' => json_encode($config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE),
            ])
            ->render();

        return $this->hidden(response($html));
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorizeCode($request);

        // Honeypot: Bots still verwerfen
        if (filled($request->input('website'))) {
            return $this->hidden(response()->json(['success' => true]));
        }

        $role = in_array($request->input('rolle'), FoundItems::ROLES, true) ? $request->input('rolle') : '';
        $group = in_array($request->input('gruppe'), FoundItems::GROUPS, true) ? $request->input('gruppe') : '';
        $lastname = $this->text($request, 'nachname', 100);
        $firstname = $this->text($request, 'vorname', 100);
        $date = $this->text($request, 'datum', 10);
        $time = $this->text($request, 'zeit', 5);
        $line = $this->text($request, 'linie', 20);
        $bus = $this->text($request, 'bus', 3);
        $location = $this->text($request, 'ort', 255);
        $item = $this->text($request, 'gegenstand', 100);
        $description = $this->text($request, 'beschreibung', 5000);
        $dropOff = $this->text($request, 'abgabe', 50);

        $lines = FoundItems::lines()[$group] ?? [];

        // Feldnamen wie im Formular (FoundItemForm.vue), Meldungen erscheinen am Feld
        $errors = array_filter([
            'rolle' => $role === '' ? '«Gefunden von» fehlt' : null,
            'gegenstand' => match (true) {
                $item === '' => 'Gegenstand fehlt',
                ! in_array($item, FoundItems::items(), true) => 'Bitte aus der Liste wählen',
                default => null,
            },
            'datum' => match (true) {
                $date === '' => 'Datum fehlt',
                ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) => 'Datum ungültig',
                default => null,
            },
            'zeit' => $time !== '' && ! preg_match('/^\d{2}:\d{2}$/', $time) ? 'Uhrzeit ungültig' : null,
            'gruppe' => $group === '' ? 'Gruppe fehlt' : null,
            'linie' => $role === 'Fahrer' && $group !== '' && ! in_array($line, $lines, true) ? 'Linie fehlt' : null,
            'bus' => $role === 'Reinigung' && ! preg_match('/^\d{1,3}$/', $bus) ? 'Bus-Nr. fehlt' : null,
            'nachname' => $lastname === '' ? 'Familienname fehlt' : null,
            'vorname' => $firstname === '' ? 'Vorname fehlt' : null,
            'abgabe' => ! in_array($dropOff, FoundItems::DROP_OFFS, true) ? 'Abgabeort fehlt' : null,
            'fotos' => $this->photoError($request),
        ]);

        if ($errors) {
            return $this->hidden(response()->json(['errors' => $errors], 422));
        }

        if ($role === 'Reinigung') {
            $line = '';
        }

        // Fahrer: "VBG 765"  ·  Reinigung (keine Linie): "VBG"
        $busLine = $role === 'Fahrer' ? "$group $line" : $group;
        $dateDisplay = preg_replace('/^(\d{4})-(\d{2})-(\d{2})$/', '$3.$2.$1', $date);
        $where = $role === 'Fahrer' ? $busLine : "$group Bus $bus (Reinigung)";
        $subject = "Neue Fundmeldung: $item – $where – $dateDisplay";

        FoundItems::ensureSetup();

        $slug = Str::slug("$item-$where-" . now()->format('Y-m-d-His')) . '-' . Str::lower(Str::random(4));
        $photos = $this->storePhotos($request, $slug);

        $entry = Entry::make()
            ->collection(FoundItems::COLLECTION)
            ->slug($slug)
            ->data([
                'title' => "$item – $where – $dateDisplay",
                // ISO-Format, damit das Datumsfeld im CP korrekt sortiert
                'submitted_at' => now()->format('Y-m-d H:i:s'),
                'role' => $role,
                'name' => trim("$lastname $firstname"), // "Nachname Vorname" wie im Dienstplan
                'date' => $dateDisplay,
                'time' => $time,
                'bus_line' => $busLine,
                'bus_number' => $bus,
                'location' => $location,
                'item' => $item,
                'description' => $description,
                'dropped_off_at' => $dropOff,
                'photos' => array_column($photos, 'path'),
                'mail_sent' => false,
            ]);
        $entry->save();

        $report = [
            'Gefunden von' => $role,
            'Name' => trim("$lastname $firstname"),
            'Datum' => $dateDisplay,
            'Uhrzeit' => $time,
            'Buslinie' => $busLine,
            'Bus-Nr' => $bus,
            'Fundort im Bus' => $location,
            'Gegenstand' => $item,
            'Beschreibung' => $description,
            'Abgegeben bei' => $dropOff,
        ];

        // Die Meldung ist gespeichert. Scheitert der Versand, steht sie im CP mit «Mail versendet: aus».
        try {
            Mail::to(config('app.found_items_email'))->send(new FoundItemNotification($role, $report, $subject, $photos));
            $entry->set('mail_sent', true)->save();
        } catch (\Throwable $e) {
            Log::error('Fundmeldung: Mail ans Fundpostfach konnte nicht versendet werden', [
                'entry' => $entry->id(),
                'error' => $e->getMessage(),
            ]);
        }

        return $this->hidden(response()->json(['success' => true]));
    }

    private function authorizeCode(Request $request): void
    {
        $code = (string) config('app.found_items_code');
        $given = $request->query('k', $request->input('k'));

        abort_unless($code !== '' && is_string($given) && hash_equals($code, $given), 404);
    }

    private function text(Request $request, string $key, int $max): string
    {
        $value = $request->input($key);

        return is_string($value) ? Str::limit(trim($value), $max, '') : '';
    }

    private function photoError(Request $request): ?string
    {
        foreach (['foto1', 'foto2'] as $field) {
            $file = $request->file($field);
            if (! $file instanceof UploadedFile || $file->getError() === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            if (! $file->isValid() || $file->getSize() > self::MAX_PHOTO_KB * 1024) {
                return 'Foto zu gross (max. 8 MB)';
            }
            if (! isset(self::PHOTO_TYPES[$file->getMimeType()])) {
                return 'Nur Fotos erlaubt';
            }
        }

        return null;
    }

    /**
     * Fotos in den privaten Asset-Container legen.
     *
     * @return list<array{disk: string, path: string, name: string, mime: string}>
     */
    private function storePhotos(Request $request, string $slug): array
    {
        $container = AssetContainer::findByHandle(FoundItems::CONTAINER);
        $photos = [];

        foreach (['foto1', 'foto2'] as $i => $field) {
            $file = $request->file($field);
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }

            $mime = $file->getMimeType();
            $ext = self::PHOTO_TYPES[$mime];
            $path = now()->format('Y/m') . "/$slug-foto" . ($i + 1) . ".$ext";

            Storage::disk($container->diskHandle())->putFileAs(dirname($path), $file, basename($path));
            $container->makeAsset($path)->save();

            $photos[] = ['disk' => $container->diskHandle(), 'path' => $path, 'name' => 'Foto' . ($i + 1) . ".$ext", 'mime' => $mime];
        }

        return $photos;
    }

    private function hidden(Response $response): Response
    {
        // Versteckte Seite: nie in Suchmaschinen, Code nicht über Referrer weitergeben
        return $response->withHeaders([
            'X-Robots-Tag' => 'noindex, nofollow, noarchive',
            'Referrer-Policy' => 'no-referrer',
            'Cache-Control' => 'no-store',
        ]);
    }
}
