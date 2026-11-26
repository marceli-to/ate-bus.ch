<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RendersHiddenPages;
use App\Support\AccidentReports;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

/**
 * Vehicle accident, «each on their own phone»: the other party (vehicle B) scans a QR code
 * on the driver's phone and fills in their part on their own phone.
 *
 * start (driver) → show + store (other party) → status (driver's phone polls and takes the data over).
 * The session lives in the cache; photos wait in a private folder until the driver sends the report.
 */
class OtherPartyController extends Controller
{
    use RendersHiddenPages;

    public const VEHICLE_TYPES = ['bicycle', 'motorcycle', 'car', 'van', 'truck', 'bus'];

    /** Upload field => file name in the report */
    private const PHOTOS = [
        'licence_front' => 'Führerausweis Unfallbeteiligter vorne',
        'licence_back' => 'Führerausweis Unfallbeteiligter hinten',
        'registration' => 'Fahrzeugausweis Unfallbeteiligter',
    ];

    // Unambiguous characters (no 0/O, 1/I), so the code can be read out and typed in
    private const CODE_ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    /** Driver: create a code for the QR code */
    public function start(Request $request): JsonResponse
    {
        $this->authorizeStaff($request);

        $data = $request->validate([
            'date' => 'nullable|string|max:10',
            'time' => 'nullable|string|max:5',
            'vehicle' => 'nullable|string|max:60',
        ]);

        $session = [
            'date' => $data['date'] ?? '',
            'time' => $data['time'] ?? '',
            'vehicle' => $data['vehicle'] ?? '',
            'done' => false,
            'data' => [],
            'files' => [],
        ];

        do {
            $code = $this->randomCode();
        } while (! Cache::add(AccidentReports::otherPartyCacheKey($code), $session, $this->ttl()));

        return $this->hidden(response()->json(['code' => $code]));
    }

    /** Driver's phone polls until the other party is done */
    public function status(Request $request, string $code): JsonResponse
    {
        $this->authorizeStaff($request);
        $session = AccidentReports::otherParty($code);
        abort_unless($session, 404);

        return $this->hidden(response()->json([
            'done' => (bool) $session['done'],
            'data' => $session['done'] ? $session['data'] : null,
            'photos' => $session['done'] ? array_values($session['files']) : [],
        ]));
    }

    /** Other party: their part of the form */
    public function show(string $code): Response
    {
        $code = strtoupper($code);
        $session = AccidentReports::otherParty($code);

        return $this->hiddenPage('accident-report', 'Unfallmeldung', url('/b'), [
            'mode' => 'other_party',
            'submitUrl' => url("/unfallmeldung/b/$code"),
            'otherParty' => [
                'code' => $code,
                'state' => ! $session ? 'invalid' : ($session['done'] ? 'done' : 'open'),
                'date' => $session['date'] ?? '',
                'time' => $session['time'] ?? '',
                'vehicle' => $session['vehicle'] ?? '',
            ],
        ], $session ? 200 : 404);
    }

    /** Other party: send their details and photos to the driver */
    public function store(Request $request, string $code): JsonResponse
    {
        $key = AccidentReports::otherPartyCacheKey($code);
        $session = Cache::get($key);

        if (! $session) {
            return $this->hidden(response()->json(['message' => 'Dieser Code ist abgelaufen. Bitte den QR-Code nochmals scannen.'], 404));
        }
        if ($session['done']) {
            return $this->hidden(response()->json(['message' => 'Die Angaben wurden bereits gesendet.'], 409));
        }

        $photo = 'required|file|mimes:jpg,jpeg,png|max:' . (int) config('accident_reports.max_file_kb');
        $data = $request->validate([
            'vehicle_type' => ['required', Rule::in(self::VEHICLE_TYPES)],
            'phone' => 'required|string|max:40',
            'owner_is_driver' => 'required|boolean',
            'owner_phone' => 'nullable|required_if:owner_is_driver,0|string|max:40',
            'email' => 'required|email|max:200',
            'maneuvers' => 'required|array|max:20',
            'maneuvers.*' => 'string|max:80',
            'damages' => 'nullable|array|max:20',
            'damages.*' => 'string|max:80',
            'damage_note' => 'nullable|string|max:300',
            'impact.x' => 'required|integer|between:0,120',
            'impact.y' => 'required|integer|between:0,222',
            'signature' => ['required', 'string', 'max:300000', 'regex:#^data:image/png;base64,[A-Za-z0-9+/=]+$#'],
            'licence_front' => $photo,
            'licence_back' => $photo,
            'registration' => $photo,
        ], [
            ...AccidentReports::VALIDATION_MESSAGES,
            ...collect(array_keys(self::PHOTOS))->flatMap(fn ($field) => [
                "$field.required" => 'Bitte alle drei Ausweise fotografieren.',
                "$field.max" => 'Ein Foto ist zu gross.',
                "$field.mimes" => 'Bitte nur Fotos (JPG oder PNG) hochladen.',
            ])->all(),
        ]);

        $folder = AccidentReports::OTHER_PARTY_FOLDER . '/' . strtoupper($code);
        $files = [];
        foreach (self::PHOTOS as $field => $name) {
            $file = $request->file($field);
            $name .= strtolower($file->getClientOriginalExtension()) === 'png' ? '.png' : '.jpg';
            Storage::disk(AccidentReports::DISK)->putFileAs($folder, $file, $name);
            $files["$folder/$name"] = $name;
        }

        $session['done'] = true;
        $session['files'] = $files;
        $session['data'] = [
            'vehicleType' => $data['vehicle_type'],
            'phone' => $data['phone'],
            'ownerIsDriver' => (bool) $data['owner_is_driver'],
            'ownerPhone' => $data['owner_phone'] ?? '',
            'email' => $data['email'],
            'maneuvers' => array_values($data['maneuvers']),
            'damages' => array_values($data['damages'] ?? []),
            'damageNote' => $data['damage_note'] ?? '',
            // The impact point is always on the vehicle type the other party chose
            'impact' => ['type' => $data['vehicle_type'], 'x' => (int) $data['impact']['x'], 'y' => (int) $data['impact']['y']],
            'signature' => $data['signature'],
        ];
        Cache::put($key, $session, $this->ttl());

        return $this->hidden(response()->json(['success' => true]));
    }

    private function randomCode(): string
    {
        $code = '';
        for ($i = 0; $i < 6; $i++) {
            $code .= self::CODE_ALPHABET[random_int(0, strlen(self::CODE_ALPHABET) - 1)];
        }

        return $code;
    }

    private function ttl(): \DateTimeInterface
    {
        return now()->addHours((int) config('accident_reports.other_party_ttl_hours'));
    }

    private function authorizeStaff(Request $request): void
    {
        abort_unless(AccidentReports::staffCodeIsValid($request->query('k', $request->input('k'))), 404);
    }
}
