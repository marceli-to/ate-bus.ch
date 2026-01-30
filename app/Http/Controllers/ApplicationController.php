<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreApplicationRequest;
use App\Http\Requests\UploadFileRequest;
use App\Mail\ApplicationConfirmation;
use App\Mail\ApplicationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Statamic\Facades\Entry;
use ZipArchive;

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

        // Generate unique application ID
        $applicationId = Str::uuid()->toString();
        $basePath = "applications/{$applicationId}";

        // Move files from temp to permanent storage
        $applicationFilePaths = $this->moveFilesFromTemp(
            $validated['application_files'],
            "{$basePath}/bewerbung"
        );

        $criminalRecordPath = $this->moveFileFromTemp(
            $validated['criminal_record'],
            "{$basePath}/strafregister"
        );

        $ivzRegisterPath = $this->moveFileFromTemp(
            $validated['ivz_register'],
            "{$basePath}/ivz"
        );

        // Create ZIP immediately
        $dossierPath = "{$basePath}/dossier.zip";
        $this->createDossierZip(
            $applicationFilePaths,
            $criminalRecordPath,
            $ivzRegisterPath,
            storage_path("app/private/{$dossierPath}")
        );

        // Get job entry for title
        $job = Entry::find($validated['job_id']);
        $jobTitle = $job ? $job->get('title') : 'Unbekannte Stelle';

        // Create Statamic entry
        $entry = Entry::make()
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
                'application_files' => json_encode($applicationFilePaths),
                'criminal_record_file' => $criminalRecordPath,
                'ivz_register_file' => $ivzRegisterPath,
                'dossier_path' => $dossierPath,
                'submitted_at' => now()->format('d.m.Y'),
            ]);

        $entry->save();

        // Update entry with dossier URL (needs entry ID)
        $entry->set('dossier_url', route('download.dossier', $entry->id()));
        $entry->save();

        // Send emails
        $applicationData = [
            'firstname' => $validated['firstname'],
            'lastname' => $validated['lastname'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'job_title' => $jobTitle,
            'entry_id' => $entry->id(),
        ];

        // Confirmation to applicant
        Mail::to($validated['email'])->send(new ApplicationConfirmation($applicationData));

        // Notification to HR
        $hrEmail = config('app.application_email');
        if ($hrEmail) {
            Mail::to($hrEmail)->send(new ApplicationNotification($applicationData));
        }

        return response()->json([
            'success' => true,
            'message' => 'Bewerbung erfolgreich eingereicht.',
        ]);
    }

    /**
     * Move multiple files from temp storage
     */
    private function moveFilesFromTemp(array $tempIds, string $destinationPath): array
    {
        $paths = [];

        foreach ($tempIds as $tempId) {
            $path = $this->moveFileFromTemp($tempId, $destinationPath);
            if ($path) {
                $paths[] = $path;
            }
        }

        return $paths;
    }

    /**
     * Move a single file from temp storage
     */
    private function moveFileFromTemp(string $tempId, string $destinationPath): ?string
    {
        $tempPath = "temp/{$tempId}";

        // Use Storage facade - it knows the correct root (storage/app/private)
        $files = Storage::disk('local')->files($tempPath);

        if (empty($files)) {
            \Log::warning("No files found in temp path", ['tempPath' => $tempPath]);
            return null;
        }

        $tempFile = $files[0];
        $filename = basename($tempFile);
        $newPath = "{$destinationPath}/{$filename}";

        // Use Storage facade to move file
        if (Storage::disk('local')->exists($tempFile)) {
            Storage::disk('local')->copy($tempFile, $newPath);
            Storage::disk('local')->deleteDirectory($tempPath);
            \Log::info("File moved successfully", ['from' => $tempFile, 'to' => $newPath]);
        } else {
            \Log::error("Source file does not exist", ['tempFile' => $tempFile]);
        }

        return $newPath;
    }

    /**
     * Create ZIP file containing all application documents
     */
    private function createDossierZip(array $applicationFiles, ?string $criminalRecordPath, ?string $ivzRegisterPath, string $zipPath): void
    {
        $dir = dirname($zipPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        // The local disk root is storage/app/private
        $storageRoot = storage_path('app/private');

        // Add application files
        foreach ($applicationFiles as $filePath) {
            $fullPath = "{$storageRoot}/{$filePath}";
            if (file_exists($fullPath)) {
                $zip->addFile($fullPath, 'bewerbung/' . basename($fullPath));
            }
        }

        // Add criminal record
        if ($criminalRecordPath) {
            $fullPath = "{$storageRoot}/{$criminalRecordPath}";
            if (file_exists($fullPath)) {
                $zip->addFile($fullPath, 'strafregister/' . basename($fullPath));
            }
        }

        // Add IVZ register
        if ($ivzRegisterPath) {
            $fullPath = "{$storageRoot}/{$ivzRegisterPath}";
            if (file_exists($fullPath)) {
                $zip->addFile($fullPath, 'ivz/' . basename($fullPath));
            }
        }

        $zip->close();
    }
}
