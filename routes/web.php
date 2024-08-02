<?php

use App\Http\Controllers\CampaignController;
use App\Http\Controllers\DataForSEOController;
use App\Http\Controllers\ProjectController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::resource('projects', ProjectController::class);
    Route::resource('campaigns', CampaignController::class);
});



Route::get('/api/oppdater/locations', [DataForSEOController::class, 'getLocations']);
Route::get('/api/oppdater/language', [DataForSEOController::class, 'getLanguages']);
