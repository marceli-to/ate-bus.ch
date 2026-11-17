<?php

namespace App\Support;

use Statamic\Facades\AssetContainer;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\Site;

/**
 * Fundmeldung (Personal): Listen für das Formular und die Statamic-Struktur.
 *
 * content/ ist nicht im Git. Collection, Asset-Container und Global Set werden
 * deshalb beim ersten Gebrauch angelegt, statt auf dem Server von Hand.
 */
class FoundItems
{
    public const COLLECTION = 'found_items';
    public const CONTAINER = 'fundmeldungen';
    public const GLOBALS = 'fundmeldung';

    public const ROLES = ['Fahrer', 'Reinigung'];
    public const GROUPS = ['VBZ', 'VBG'];
    public const DROP_OFFS = ['Kiste', 'Aufenthaltsraum', 'Dispo', 'Tresor'];

    /** Startwerte für das Global Set (danach im Control Panel pflegbar) */
    public const DEFAULT_ITEMS = [
        'Handy', 'Portemonnaie / Geld', 'Karte / Ausweis', 'Schlüssel', 'Tasche / Rucksack',
        'Kleidung', 'Mütze / Handschuhe / Schal', 'Brille', 'Kopfhörer / AirPods', 'Schirm', 'Spielzeug',
        'Schmuck / Uhr', 'Dokumente', 'Sonstiges',
    ];

    /**
     * Linien aus der Collection «Buslinien», gruppiert nach Partner.
     * Titel «VBG 765» → ['VBG' => ['765', ...]]
     * Enthält auch Linien mit «Nur intern» (die in «Verlust melden» fehlen).
     *
     * @return array<string, list<string>>
     */
    public static function lines(): array
    {
        $lines = array_fill_keys(self::GROUPS, []);

        Entry::query()->where('collection', 'bus_routes')->get()
            ->filter(fn ($entry) => $entry->published())
            ->each(function ($entry) use (&$lines) {
                $group = strtoupper((string) $entry->get('partner'));
                if (! isset($lines[$group])) {
                    return;
                }
                $line = trim(preg_replace('/^' . $group . '\s+/i', '', (string) $entry->get('title')));
                if ($line !== '') {
                    $lines[$group][] = $line;
                }
            });

        foreach ($lines as &$list) {
            $list = array_values(array_unique($list));
            sort($list, SORT_NATURAL);
        }

        return $lines;
    }

    /** @return list<string> */
    public static function items(): array
    {
        $items = GlobalSet::findByHandle(self::GLOBALS)?->inDefaultSite()?->get('items');
        $items = array_values(array_filter(array_map('trim', (array) $items), 'strlen'));

        return $items ?: self::DEFAULT_ITEMS;
    }

    public static function ensureSetup(): void
    {
        if (! Collection::findByHandle(self::COLLECTION)) {
            Collection::make(self::COLLECTION)
                ->title('Fundmeldungen')
                ->sortField('submitted_at')
                ->sortDirection('desc')
                ->save();
        }

        if (! AssetContainer::findByHandle(self::CONTAINER)) {
            AssetContainer::make(self::CONTAINER)
                ->title('Fundmeldungen')
                ->disk('found_items')
                ->save();
        }

        if (! GlobalSet::findByHandle(self::GLOBALS)) {
            $set = GlobalSet::make(self::GLOBALS)->title('Fundmeldung');
            $set->addLocalization($set->makeLocalization(Site::default()->handle())->data([
                'items' => self::DEFAULT_ITEMS,
            ]));
            $set->save();
        }
    }
}
