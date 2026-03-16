<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Statamic\Facades\Entry;
use Statamic\Facades\User;

class DownloadController extends Controller
{
    /**
     * Download application dossier (ZIP) — requires CP authentication
     */
    public function dossier(Request $request, string $id)
    {
        // Check if user is authenticated in Statamic CP
        $user = User::current();
        if (!$user) {
            abort(403, 'Unauthorized');
        }

        return $this->streamDossier($id);
    }

    /**
     * Download application dossier (ZIP) via signed URL — no login required
     */
    public function dossierSigned(Request $request, string $id)
    {
        // The 'signed' middleware already validated the URL signature
        return $this->streamDossier($id);
    }

    private function streamDossier(string $id)
    {
        $entry = Entry::query()
            ->where('collection', 'applications')
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
