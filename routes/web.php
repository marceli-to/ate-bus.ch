<?php

use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\LostAndFoundController;
use Illuminate\Support\Facades\Route;

// Application form API routes
Route::prefix('api/applications')->group(function () {
    Route::post('/upload', [ApplicationController::class, 'upload']);
    Route::delete('/upload', [ApplicationController::class, 'deleteUpload']);
    Route::post('/', [ApplicationController::class, 'store']);
});

// Lost and found form API routes
Route::post('/api/lost-and-found', [LostAndFoundController::class, 'store']);

// Bus routes API
Route::get('/api/bus-routes', function () {
    return \Statamic\Facades\Entry::query()
        ->where('collection', 'bus_routes')
        ->orderBy('title')
        ->get()
        ->map(fn ($entry) => [
            'title' => $entry->get('title'),
            'route' => $entry->get('route'),
            'partner' => $entry->get('partner'),
        ]);
});

// Authenticated route for dossier download
Route::get('/download/bewerbung/{id}', [DownloadController::class, 'dossier'])
    ->name('download.dossier')
    ->middleware('statamic.cp.authenticated');
