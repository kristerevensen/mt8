<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ConsentCheckerController extends Controller
{
    /**
     * Vis hovedsiden for consent checker
     */
    public function index()
    {
        return Inertia::render('ConsentChecker/Index');
    }

    /**
     * Analyser en nettside for consent
     */
    public function analyze(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'url' => 'required|url'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Ugyldig URL format'
            ], 422);
        }

        try {
            $client = new Client([
                'verify' => false,
                'timeout' => 10,
                'allow_redirects' => true,
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
                ]
            ]);

            $response = $client->get($request->url);
            $body = (string) $response->getBody();

            // Sjekk HTTPS
            $hasHttps = str_starts_with(strtolower($request->url), 'https://');

            // Sjekk for cookie policy
            $hasCookiePolicy = preg_match('/cookie\s+policy|cookiepolicy|privacy\s+policy|personvern/i', $body) === 1;

            // Sjekk for consent manager
            $hasConsentManager =
                str_contains($body, 'cookiebot') ||
                str_contains($body, 'onetrust') ||
                str_contains($body, 'cookie-consent') ||
                str_contains($body, 'CookieConsent') ||
                str_contains($body, 'gdpr') ||
                str_contains($body, 'cookie-notice') ||
                str_contains($body, 'cookie_notice') ||
                preg_match('/consent\s+manager|samtykke/i', $body) === 1;

            return response()->json([
                'compliant' => $hasHttps && $hasCookiePolicy && $hasConsentManager,
                'details' => [
                    'https' => $hasHttps,
                    'cookiePolicy' => $hasCookiePolicy,
                    'consentManager' => $hasConsentManager,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Consent checker error: ' . $e->getMessage(), [
                'url' => $request->url,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Kunne ikke analysere nettstedet. Vennligst sjekk URL-en og prøv igjen.'
            ], 500);
        }
    }
}
