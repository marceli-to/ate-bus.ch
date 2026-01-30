<?php

use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\DownloadController;
use Illuminate\Support\Facades\Route;

// Application form API routes
Route::prefix('api/applications')->group(function () {
    Route::post('/upload', [ApplicationController::class, 'upload']);
    Route::delete('/upload', [ApplicationController::class, 'deleteUpload']);
    Route::post('/', [ApplicationController::class, 'store']);
});

// Authenticated route for dossier download
Route::get('/download/bewerbung/{id}', [DownloadController::class, 'dossier'])
    ->name('download.dossier')
    ->middleware('statamic.cp.authenticated');
