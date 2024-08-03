<?php

use App\Http\Controllers\CampaignController;
use App\Http\Controllers\CampaignLinkController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\DataController;
use App\Http\Controllers\DataForSEOController;
use App\Http\Controllers\GrowthController;
use App\Http\Controllers\KeywordController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\TechnicalController;
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
    Route::resource('campaign-links', CampaignLinkController::class);
    Route::resource('pages', DataController::class)->only([
        'index',
        'show',
        'create',
        'store',
        'edit',
        'update',
        'destroy',
    ]);

    Route::resource('technical', TechnicalController::class);
    Route::resource('content', ContentController::class);
    Route::resource('ranking', RankingController::class);
    Route::resource('keywords', KeywordController::class);

    Route::get('/growth/insights', [GrowthController::class, 'insights']);
    Route::resource('growth', GrowthController::class);
});


Route::get('/api/oppdater/locations', [DataForSEOController::class, 'getLocations']);
Route::get('/api/oppdater/language', [DataForSEOController::class, 'getLanguages']);
