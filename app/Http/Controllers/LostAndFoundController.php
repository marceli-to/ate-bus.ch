<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLostAndFoundRequest;
use App\Mail\LostAndFoundConfirmation;
use App\Mail\LostAndFoundNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Statamic\Facades\Entry;

class LostAndFoundController extends Controller
{
    /**
     * Store a new lost and found report
     */
    public function store(StoreLostAndFoundRequest $request)
    {
        $validated = $request->validated();

        // Create Statamic entry
        $entry = Entry::make()
            ->collection('lost_and_found')
            ->slug(Str::slug("{$validated['firstname']}-{$validated['lastname']}-" . now()->format('Y-m-d-His')))
            ->data([
                'title' => "{$validated['firstname']} {$validated['lastname']}",
                'gender' => $validated['gender'],
                'firstname' => $validated['firstname'],
                'lastname' => $validated['lastname'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'date' => $validated['date'],
                'time' => $validated['time'],
                'bus_line' => $validated['bus_line'],
                'description' => $validated['description'],
                'submitted_at' => now()->format('d.m.Y H:i'),
            ]);

        $entry->save();

        // Prepare email data
        $reportData = [
            'firstname' => $validated['firstname'],
            'lastname' => $validated['lastname'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'date' => Carbon::parse($validated['date'])->format('d.m.Y'),
            'time' => $validated['time'],
            'bus_line' => $validated['bus_line'],
            'description' => $validated['description'],
        ];

        // Confirmation to reporter
        Mail::to($validated['email'])->send(new LostAndFoundConfirmation($reportData));

        // Notification to lost and found office
        $officeEmail = config('app.lost_and_found_email');
        if ($officeEmail) {
            Mail::to($officeEmail)->send(new LostAndFoundNotification($reportData));
        }

        return response()->json([
            'success' => true,
            'message' => 'Verlustmeldung erfolgreich eingereicht.',
        ]);
    }
}
