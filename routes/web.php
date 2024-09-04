<?php

use App\Http\Controllers\CampaignController;
use App\Http\Controllers\CampaignLinkController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\DataController;
use App\Http\Controllers\DataForSEOController;
use App\Http\Controllers\GoalsController;
use App\Http\Controllers\GrowthController;
use App\Http\Controllers\KeywordController;
use App\Http\Controllers\KeywordListController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\TechnicalController;
use App\Mail\Invitation;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Mail;
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

    /** Projects **/
    Route::get('projects/{project}/settings', [ProjectController::class, 'settings'])->name('projects.settings');
    Route::resource('projects', ProjectController::class);

    /** Campaigns **/
    //Route::get('campaigns/{campaign}', [CampaignController::class, 'show'])->name('campaigns.show');
    Route::resource('campaigns', CampaignController::class);
    Route::get('campaigns/create', [CampaignController::class, 'create'])->name('campaigns.create');


    /** Campaign Links **/
    Route::resource('campaign-links', CampaignLinkController::class);
    Route::get('/campaign-links/create', [CampaignLinkController::class, 'create'])->name('campaign-links.create');
    Route::get('/campaign-links/{link_token}/copy', [CampaignLinkController::class, 'copy'])->name('campaign-links.copy');

    Route::resource('pages', DataController::class)->only([
        'index',
        'show',
        'create',
        'store',
        'edit',
        'update',
        'destroy',
    ]);

    /** Technical **/
    Route::resource('technical', TechnicalController::class);

    /** Content **/
    Route::resource('content', ContentController::class);

    /** Ranking **/
    Route::resource('ranking', RankingController::class);

    /** Keywords **/
    Route::resource('keywords', KeywordController::class);

    Route::resource('keyword-lists', KeywordListController::class)->except(['show']);
    Route::get('keyword-lists/{list_uuid}', [KeywordListController::class, 'show'])->name('keyword-lists.show');
    Route::get('keyword-lists/create', [KeywordListController::class, 'create'])->name('keyword-lists.create');


    /** Growth **/
    Route::get('/growth/insights', [GrowthController::class, 'insights']);
    Route::resource('growth', GrowthController::class);

    Route::resource('goals', GoalsController::class);
});

/** API  **/
Route::get('/api/oppdater/locations', [DataForSEOController::class, 'getLocations']);
Route::get('/api/oppdater/language', [DataForSEOController::class, 'getLanguages']);

Route::get('/invitation', function () {
    $name = 'Knut';
    Mail::to('k@mcminn.no')->send(new Invitation($name));
})->name('invitation');
