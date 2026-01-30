<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Statamic\Facades\Entry;
use Statamic\Facades\User;

class DownloadController extends Controller
{
    /**
     * Download application dossier (ZIP)
     */
    public function dossier(Request $request, string $id)
    {
        // Check if user is authenticated in Statamic CP
        $user = User::current();
        if (!$user) {
            abort(403, 'Unauthorized');
        }

        $entry = Entry::query()
            ->where('collection', 'bewerbungen')
            ->where('id', $id)
            ->first();

        if (!$entry) {
            abort(404, 'Bewerbung nicht gefunden');
        }

        $dossierPath = $entry->get('dossier_path');

        if (!$dossierPath) {
            abort(404, 'Dossier nicht gefunden');
        }

        $fullPath = storage_path("app/private/{$dossierPath}");

        if (!file_exists($fullPath)) {
            abort(404, 'Dossier-Datei nicht gefunden');
        }

        $filename = "bewerbung-{$entry->get('firstname')}-{$entry->get('lastname')}.zip";

        return response()->download($fullPath, $filename);
    }
}
