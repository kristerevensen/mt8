<?php

use App\Http\Controllers\DataForSEOController;
use App\Http\Controllers\KeywordController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SearchConsoleController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/pingback', [DataForSEOController::class, 'handlePingback']);
Route::get('/pingback', [DataForSEOController::class, 'handlePingback']);
Route::post('/keywords/add-to-list', [KeywordController::class, 'addToList'])->name('keywords.add_to_list');


Route::get('/search-console-data', [SearchConsoleController::class, 'index']);
Route::post('/search-console/fetch-data', [SearchConsoleController::class, 'fetchData']);
