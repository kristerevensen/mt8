<?php

use App\Http\Controllers\CampaignController;
use App\Http\Controllers\CampaignLinkController;
use App\Http\Controllers\ConsentCheckerController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\DataController;
use App\Http\Controllers\DataForSEOController;
use App\Http\Controllers\GoalsController;
use App\Http\Controllers\GrowthController;
use App\Http\Controllers\KeywordController;
use App\Http\Controllers\KeywordListController;
use App\Http\Controllers\MarketingHealthCheckerController;
use App\Http\Controllers\OptimizationController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\SearchConsoleController;
use App\Http\Controllers\TechnicalController;
use App\Http\Controllers\WebsiteSpyController;
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

    Route::prefix('search-console')->name('search-console.')->group(function () {
        Route::get('/', [SearchConsoleController::class, 'index'])->name('index');
        Route::get('/connect', [SearchConsoleController::class, 'connect'])->name('connect');
        Route::get('/callback', [SearchConsoleController::class, 'callback'])->name('callback');
        Route::post('/disconnect', [SearchConsoleController::class, 'disconnect'])->name('disconnect');
        Route::get('/fetch', [SearchConsoleController::class, 'fetchDataPage'])->name('fetch');
        Route::post('/fetch', [SearchConsoleController::class, 'fetchData'])->name('fetch.store');
    });

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

    Route::get('/gsc/fetch', [SearchConsoleController::class, 'fetchDataPage'])->name('gsc.fetch');
    Route::post('/gsc/fetch-data', [SearchConsoleController::class, 'fetchData'])->name('gsc.fetch-data');
    Route::get('/gsc/overview', [SearchConsoleController::class, 'index'])->name('gsc.index');


    Route::post('/pingback', [DataForSEOController::class, 'handlePingback']);
    Route::get('/pingback', [DataForSEOController::class, 'handlePingback']);


    Route::resource('pages', DataController::class)->only([
        'index',
        'show',
        'create',
        'store',
        'edit',
        'update',
        'destroy',
    ]);

    // open route for consent checker
    Route::get('/consent-checker', [ConsentCheckerController::class, 'index'])
    ->name('consent-checker');
    Route::post('/consent-checker/analyze', [ConsentCheckerController::class, 'analyze'])
    ->name('consent-checker.analyze');

    Route::get('/marketing-health-checker', [MarketingHealthCheckerController::class, 'index'])
    ->name('marketing-health-checker');
    Route::post('/marketing-health-checker/analyze', [MarketingHealthCheckerController::class, 'analyze'])
    ->name('marketing-health-checker.analyze');


    /** Technical **/
    Route::resource('technical', TechnicalController::class);

    /** Website Spy Routes **/
    Route::get('website-spy', [WebsiteSpyController::class, 'index'])->name('website-spy.index');
    Route::post('website-spy', [WebsiteSpyController::class, 'spy'])->name('website-spy.spy');
    Route::get('website-spy/{uuid}', [WebsiteSpyController::class, 'show'])->name('website-spy.show');
    Route::post('website-spy/analyze', [WebsiteSpyController::class, 'analyze'])->name('website-spy.analyze');
    Route::get('website-spy/analysis/{uuid}', [WebsiteSpyController::class, 'showAnalysis'])->name('website-spy.show-analysis');
    Route::post('website-spy/technologies', [WebsiteSpyController::class, 'fetchDomainTechnologies'])->name('website-spy.technologies');
    Route::delete('website-spy/competitors/{uuid}/{id}', [WebsiteSpyController::class, 'deleteCompetitor'])->name('website-spy.delete-competitor');

    /** Content **/
    Route::resource('content', ContentController::class);

    /** Ranking **/
    Route::resource('ranking', RankingController::class);

    /** Keywords **/
    Route::resource('keywords', KeywordController::class);

    Route::resource('keyword-lists', KeywordListController::class)->except(['show']);
    Route::get('keyword-lists/{list_uuid}', [KeywordListController::class, 'show'])->name('keyword-lists.show');
    Route::get('keyword-lists/create', [KeywordListController::class, 'create'])->name('keyword-lists.create');
    Route::post('/keywords/add-to-list', [KeywordController::class, 'addToList'])->name('keywords.add_to_list');
    Route::post('/keywords/bulk-delete', [KeywordController::class, 'bulkDelete'])->name('keywords.bulk_delete');
    Route::post('/import-keywords', [KeywordController::class, 'importKeywordsFromJson']);
    Route::post('/keywords/website/get', [KeywordController::class, 'getWebsiteKeywords'])->name('website.keywords');

    Route::resource('optimization', OptimizationController::class);


    /** Growth **/
    Route::get('/growth/insights', [GrowthController::class, 'insights']);
    Route::resource('growth', GrowthController::class);

    Route::resource('goals', GoalsController::class);
});

// Search Console routes
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    Route::prefix('search-console')->name('search-console.')->group(function () {
        Route::get('/', [SearchConsoleController::class, 'index'])->name('index');
        Route::get('/fetch', [SearchConsoleController::class, 'fetchDataPage'])->name('fetch');
        Route::post('/fetch', [SearchConsoleController::class, 'fetchData'])->name('fetch.store');
        Route::get('/historical', [SearchConsoleController::class, 'getHistoricalData'])->name('historical');
    });
});

/** API  **/
Route::get('/api/oppdater/locations', [DataForSEOController::class, 'getLocations']);
Route::get('/api/oppdater/language', [DataForSEOController::class, 'getLanguages']);

Route::get('/invitation', function () {
    $name = 'Knut';
    Mail::to('k@mcminn.no')->send(new Invitation($name));
})->name('invitation');
