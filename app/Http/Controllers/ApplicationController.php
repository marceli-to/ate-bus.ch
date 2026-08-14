<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreApplicationRequest;
use App\Http\Requests\UploadFileRequest;
use App\Mail\ApplicationConfirmation;
use App\Mail\ApplicationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Statamic\Facades\Entry;

class ApplicationController extends Controller
{
    /**
     * Handle FilePond temporary file upload
     */
    public function upload(UploadFileRequest $request)
    {
        $file = $request->getUploadedFile();
        $tempId = Str::uuid()->toString();
        $filename = $file->getClientOriginalName();

        $file->storeAs(
            "temp/{$tempId}",
            $filename,
            'local'
        );

        return response()->json([
            'id' => $tempId,
            'filename' => $filename,
        ]);
    }

    /**
     * Handle FilePond temporary file deletion
     */
    public function deleteUpload(Request $request)
    {
        $tempId = $request->getContent();

        if ($tempId) {
            Storage::disk('local')->deleteDirectory("temp/{$tempId}");
        }

        return response()->json(['success' => true]);
    }

    /**
     * Store a new application
     */
    public function store(StoreApplicationRequest $request)
    {
        $validated = $request->validated();

        $applicationId = Str::uuid()->toString();

        // Collect file paths from temp storage
        $files = $this->collectTempFiles($validated);

        // Get job entry for title
        $job = Entry::find($validated['job_id']);
        $jobTitle = $job ? $job->get('title') : 'Unbekannte Stelle';

        // Create Statamic entry
        $entry = Entry::make()
            ->id($applicationId)
            ->collection('applications')
            ->slug(Str::slug("{$validated['firstname']}-{$validated['lastname']}-" . now()->format('Y-m-d-His')))
            ->data([
                'title' => "{$validated['firstname']} {$validated['lastname']}",
                'job_id' => $validated['job_id'],
                'gender' => $validated['gender'],
                'firstname' => $validated['firstname'],
                'lastname' => $validated['lastname'],
                'street' => $validated['street'],
                'zip' => $validated['zip'],
                'city' => $validated['city'],
                'phone' => $validated['phone'],
                'email' => $validated['email'],
                'german_skills' => $validated['german_skills'],
                'permit' => $validated['permit'],
                // ISO-Format, damit das Datumsfeld im CP korrekt sortiert
                'submitted_at' => now()->format('Y-m-d H:i:s'),
            ]);

        $entry->save();

        // Send emails
        $applicationData = [
            'gender' => $validated['gender'],
            'firstname' => $validated['firstname'],
            'lastname' => $validated['lastname'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'job_title' => $jobTitle,
        ];

        try {
            Mail::to($validated['email'])->send(new ApplicationConfirmation($applicationData));
        } catch (\Throwable $e) {
            Log::error('Bewerbung: Bestätigungsmail konnte nicht versendet werden', [
                'entry' => $applicationId,
                'recipient' => $validated['email'],
                'error' => $e->getMessage(),
            ]);
        }

        // Temp-Dateien nur löschen, wenn das Dossier per Mail beim HR angekommen
        // ist – sonst sind die Uploads unwiederbringlich weg.
        $notificationSent = true;

        $hrEmail = config('app.application_email');
        if ($hrEmail) {
            try {
                Mail::to($hrEmail)->send(new ApplicationNotification($applicationData, $files));
            } catch (\Throwable $e) {
                $notificationSent = false;
                Log::error('Bewerbung: Benachrichtigung ans HR konnte nicht versendet werden', [
                    'entry' => $applicationId,
                    'recipient' => $hrEmail,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Clean up temp files
        if ($notificationSent) {
            $this->cleanupTempFiles($validated);
        }

        return response()->json([
            'success' => true,
            'message' => 'Bewerbung erfolgreich eingereicht.',
        ]);
    }

    private function collectTempFiles(array $validated): array
    {
        $files = [];

        foreach ($validated['application_files'] as $tempId) {
            $file = $this->getTempFile($tempId);
            if ($file) {
                $files[] = $file;
            }
        }

        $file = $this->getTempFile($validated['criminal_record']);
        if ($file) {
            $files[] = $file;
        }

        $file = $this->getTempFile($validated['ivz_register']);
        if ($file) {
            $files[] = $file;
        }

        return $files;
    }

    private function getTempFile(string $tempId): ?array
    {
        $tempFiles = Storage::disk('local')->files("temp/{$tempId}");

        if (empty($tempFiles)) {
            return null;
        }

        return [
            'path' => storage_path("app/private/{$tempFiles[0]}"),
            'name' => basename($tempFiles[0]),
        ];
    }

    private function cleanupTempFiles(array $validated): void
    {
        foreach ($validated['application_files'] as $tempId) {
            Storage::disk('local')->deleteDirectory("temp/{$tempId}");
        }

        Storage::disk('local')->deleteDirectory("temp/{$validated['criminal_record']}");
        Storage::disk('local')->deleteDirectory("temp/{$validated['ivz_register']}");
    }
}
