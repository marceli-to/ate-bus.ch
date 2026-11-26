<?php

use App\Http\Controllers\AccidentReportController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\FoundItemController;
use App\Http\Controllers\LostAndFoundController;
use App\Http\Controllers\OtherPartyController;
use Illuminate\Support\Facades\Route;

// Application form API routes
Route::prefix('api/applications')->group(function () {
    Route::post('/upload', [ApplicationController::class, 'upload']);
    Route::delete('/upload', [ApplicationController::class, 'deleteUpload']);
    Route::post('/', [ApplicationController::class, 'store']);
});

// Lost and found form API routes
Route::post('/api/lost-and-found', [LostAndFoundController::class, 'store']);

// Fundmeldung (Personal, nur mit Code im Link aus der Infosoft-App)
Route::get('/fundmeldung', [FoundItemController::class, 'show'])->middleware('throttle:60,1');
Route::post('/fundmeldung', [FoundItemController::class, 'store'])->middleware('throttle:30,10');

// Accident report (hidden): staff with the code in the link, passengers via the QR code on the business card,
// the other party of a vehicle accident via the QR code on the driver's phone (or /b to type in the code).
// Each limiter has its own prefix: without it all throttled routes share one counter per IP,
// and the driver's phone polling the status every 3 s would block sending the report.
Route::get('/unfallmeldung', [AccidentReportController::class, 'staff'])->middleware('throttle:60,1,accident-page');
Route::post('/unfallmeldung', [AccidentReportController::class, 'storeStaff'])->middleware('throttle:12,10,accident-submit');
Route::get('/unfall-fahrgast', [AccidentReportController::class, 'passenger'])->middleware('throttle:60,1,accident-page');
Route::post('/unfall-fahrgast', [AccidentReportController::class, 'storePassenger'])->middleware('throttle:12,10,accident-submit');
Route::get('/b', [AccidentReportController::class, 'codeEntry'])->middleware('throttle:60,1,accident-page');
Route::post('/unfallmeldung/b', [OtherPartyController::class, 'start'])->middleware('throttle:30,10,accident-qr-start');
Route::get('/unfallmeldung/b/{code}', [OtherPartyController::class, 'show'])->where('code', '[A-Za-z0-9]{6}')->middleware('throttle:60,1,accident-page');
Route::post('/unfallmeldung/b/{code}', [OtherPartyController::class, 'store'])->where('code', '[A-Za-z0-9]{6}')->middleware('throttle:12,10,accident-qr-submit');
Route::get('/unfallmeldung/b/{code}/status', [OtherPartyController::class, 'status'])->where('code', '[A-Za-z0-9]{6}')->middleware('throttle:60,1,accident-qr-status');

// Bus routes API
Route::get('/api/bus-routes', function () {
    return \Statamic\Facades\Entry::query()
        ->where('collection', 'bus_routes')
        ->orderBy('title')
        ->get()
        // «Nur intern»: nur für die Fundmeldung, nicht für Fahrgäste
        ->reject(fn ($entry) => (bool) $entry->get('internal'))
        ->values()
        ->map(fn ($entry) => [
            'title' => $entry->get('title'),
            'route' => $entry->get('route'),
            'partner' => $entry->get('partner'),
        ]);
});

// Authenticated route for dossier download (CP users)
Route::get('/download/bewerbung/{id}', [DownloadController::class, 'dossier'])
    ->name('download.dossier')
    ->middleware('statamic.cp.authenticated');

// Signed URL route for dossier download (no login required)
Route::get('/download/bewerbung/dossier/{id}', [DownloadController::class, 'dossierSigned'])
    ->name('download.dossier.signed')
    ->middleware('signed');
