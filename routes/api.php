<?php

use App\Http\Controllers\DataForSEOController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SearchConsoleController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/pingback', [DataForSEOController::class, 'handlePingback']);


Route::get('/search-console-data', [SearchConsoleController::class, 'index']);
Route::post('/search-console/fetch-data', [SearchConsoleController::class, 'fetchData']);
