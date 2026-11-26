<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Collection;

/**
 * Accident reports (Unfallmeldung): report types, fleet and line data, Statamic structure.
 *
 * content/ is not in git, so the collection and asset container are created on first use
 * instead of by hand on the server (same approach as FoundItems).
 */
class AccidentReports
{
    public const COLLECTION = 'accident_reports';
    public const CONTAINER = 'unfallmeldungen';
    public const DISK = 'accident_reports';

    // Folder on the disk for photos the other party uploads before the report is sent
    public const OTHER_PARTY_FOLDER = 'other-party';

    /** Report types and their label in the control panel */
    public const TYPES = [
        'vehicle' => 'Unfall Fahrzeug',
        'passenger' => 'Unfall Fahrgast',
        'passenger_self' => 'Unfall Fahrgast (vom Fahrgast)',
        'passenger_card' => 'Unfall Fahrgast – Vormeldung',
        'staff' => 'Unfall Mitarbeiter/in',
    ];

    /** Types sent from the staff page (code in the link) */
    public const STAFF_TYPES = ['vehicle', 'passenger', 'passenger_card', 'staff'];

    /** Types sent from the public passenger page */
    public const PASSENGER_TYPES = ['passenger_self'];

    /**
     * Fallback validation messages (the site has no German validation translations).
     * The form checks everything before sending, so these only show if that was bypassed.
     */
    public const VALIDATION_MESSAGES = [
        'required' => 'Bitte alle Pflichtangaben ausfüllen.',
        'required_if' => 'Bitte alle Pflichtangaben ausfüllen.',
        'email' => 'Bitte eine gültige E-Mail-Adresse angeben.',
        'max' => 'Eine Angabe ist zu lang.',
        'mimes' => 'Bitte nur Fotos (JPG oder PNG) hochladen.',
        'file' => 'Ein Foto konnte nicht hochgeladen werden.',
        'uploaded' => 'Ein Foto konnte nicht hochgeladen werden.',
        ...[
            'in' => 'Eine Angabe ist ungültig.', 'string' => 'Eine Angabe ist ungültig.', 'integer' => 'Eine Angabe ist ungültig.',
            'between' => 'Eine Angabe ist ungültig.', 'regex' => 'Eine Angabe ist ungültig.', 'array' => 'Eine Angabe ist ungültig.',
            'boolean' => 'Eine Angabe ist ungültig.', 'alpha_num' => 'Eine Angabe ist ungültig.', 'size' => 'Eine Angabe ist ungültig.',
        ],
    ];

    /** @return list<array<string, mixed>> */
    public static function vehicles(): array
    {
        return self::data('vehicles.json');
    }

    /** @return array<string, array{network: string, directions: array<string, list<string>>}> */
    public static function lines(): array
    {
        return self::data('lines.json');
    }

    /** Files per request: config, but never more than PHP accepts (max_file_uploads) */
    public static function maxFiles(): int
    {
        $php = (int) ini_get('max_file_uploads') ?: 20;

        return min((int) config('accident_reports.max_files'), $php);
    }

    public static function recipient(string $type): string
    {
        return (string) config("accident_reports.recipients.$type", config('accident_reports.recipients.vehicle'));
    }

    public static function staffCodeIsValid(mixed $given): bool
    {
        $code = (string) config('accident_reports.code');

        return $code !== '' && is_string($given) && hash_equals($code, $given);
    }

    public static function otherPartyCacheKey(string $code): string
    {
        return 'accident_report_other_party_' . strtoupper($code);
    }

    /** @return array<string, mixed>|null */
    public static function otherParty(string $code): ?array
    {
        return Cache::get(self::otherPartyCacheKey($code));
    }

    public static function ensureSetup(): void
    {
        if (! Collection::findByHandle(self::COLLECTION)) {
            Collection::make(self::COLLECTION)
                ->title('Unfallmeldungen')
                ->sortField('submitted_at')
                ->sortDirection('desc')
                ->save();
        }

        if (! AssetContainer::findByHandle(self::CONTAINER)) {
            AssetContainer::make(self::CONTAINER)
                ->title('Unfallmeldungen')
                ->disk(self::DISK)
                ->save();
        }
    }

    private static function data(string $file): array
    {
        static $cache = [];

        return $cache[$file] ??= json_decode(
            file_get_contents(resource_path("data/accident-reports/$file")),
            true,
            flags: JSON_THROW_ON_ERROR,
        );
    }
}
