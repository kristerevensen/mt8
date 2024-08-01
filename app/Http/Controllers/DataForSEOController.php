<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Location;
use App\Models\Language;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;

class DataForSEOController extends Controller
{
    /**
     * Fetch locations from DataForSEO and store them in the database.
     */
    public function getLocations()
    {
        $baseUrl = Config::get('dataforseo.base_url');
        $login = Config::get('dataforseo.login');
        $password = Config::get('dataforseo.password');

        $credentials = base64_encode("{$login}:{$password}");

        // Fetch locations
        $locationsResponse = Http::withOptions([
            'verify' => false,
        ])->withHeaders([
            'Authorization' => "Basic {$credentials}",
            'Content-Type' => 'application/json',
        ])->get("{$baseUrl}/v3/content_analysis/locations");

        if ($locationsResponse->successful()) {
            $locations = $locationsResponse->json('tasks.0.result');
            foreach ($locations as $location) {
                Location::updateOrCreate(
                    ['country_iso_code' => $location['country_iso_code']],
                    ['location_name' => $location['location_name']]
                );
            }
            return response()->json(['message' => 'Locations fetched and stored successfully']);
        } else {
            return response()->json(['error' => 'Failed to fetch locations'], 500);
        }
    }
    /**
     * Fetch languages from DataForSEO and store them in the database.
     */
    public function getLanguages()
    {
        $baseUrl = Config::get('dataforseo.base_url');
        $login = Config::get('dataforseo.login');
        $password = Config::get('dataforseo.password');

        $credentials = base64_encode("{$login}:{$password}");

        // Assuming there is an endpoint for languages (this is hypothetical)
        $languagesResponse = Http::withOptions([
            'verify' => false,
        ])->withHeaders([
            'Authorization' => "Basic {$credentials}",
            'Content-Type' => 'application/json',
        ])->get("{$baseUrl}/v3/content_analysis/languages"); // Hypothetical endpoint

        if ($languagesResponse->successful()) {
            $languages = $languagesResponse->json('tasks.0.result');
            foreach ($languages as $language) {
                Language::updateOrCreate(
                    ['language_code' => $language['language_code']],
                    ['language_name' => $language['language_name']]
                );
            }
            return response()->json(['message' => 'Languages fetched and stored successfully']);
        } else {
            return response()->json(['error' => 'Failed to fetch languages'], 500);
        }
    }
}
