<?php

namespace App\Services;

use Google\Client;
use Google\Service\SearchConsole;
use Google\Service\SearchConsole\SearchAnalyticsQueryRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class GoogleSearchConsoleService
{
    protected $client;
    protected $searchConsole;

    public function __construct()
    {
        try {
            $credentialsPath = storage_path('app/google/credentials.json');
            
            // Check if credentials file exists
            if (!file_exists($credentialsPath)) {
                Log::error('Google credentials file not found at: ' . $credentialsPath);
                throw new \Exception('Google credentials file not found');
            }

            // Check if credentials file is readable
            if (!is_readable($credentialsPath)) {
                Log::error('Google credentials file is not readable at: ' . $credentialsPath);
                throw new \Exception('Google credentials file is not readable');
            }

            // Try to decode the JSON to ensure it's valid
            $credentials = json_decode(file_get_contents($credentialsPath), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Invalid JSON in credentials file: ' . json_last_error_msg());
                throw new \Exception('Invalid credentials file format');
            }

            // Check required fields in credentials
            if (!isset($credentials['web'])) {
                Log::error('Missing "web" configuration in credentials file');
                throw new \Exception('Invalid credentials configuration');
            }

            $this->client = new Client();
            $this->client->setAuthConfig($credentialsPath);
            $this->client->addScope('https://www.googleapis.com/auth/webmasters.readonly');
            $this->client->setAccessType('offline');
            $this->client->setPrompt('consent');
            $this->client->setIncludeGrantedScopes(true);
            
            // Set the redirect URI using the current host
            $baseUrl = config('app.url');
            $baseUrl = rtrim($baseUrl, '/');
            $redirectUri = $baseUrl . '/search-console/callback';
            
            Log::info('Setting redirect URI: ' . $redirectUri);
            $this->client->setRedirectUri($redirectUri);
            
            // Load the access token from session if it exists
            if (Session::has('google_token')) {
                Log::info('Found existing token in session');
                $token = Session::get('google_token');
                $this->client->setAccessToken($token);
                
                // Refresh token if it's expired
                if ($this->client->isAccessTokenExpired()) {
                    Log::info('Token is expired, attempting refresh');
                    if ($this->client->getRefreshToken()) {
                        try {
                            $token = $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
                            Session::put('google_token', $token);
                            Log::info('Token refreshed successfully');
                        } catch (\Exception $e) {
                            Log::error('Error refreshing token: ' . $e->getMessage());
                            Session::forget('google_token');
                        }
                    } else {
                        Log::error('No refresh token available');
                        Session::forget('google_token');
                    }
                }
            } else {
                Log::info('No existing token found in session');
            }
            
            $this->searchConsole = new SearchConsole($this->client);
            
        } catch (\Exception $e) {
            Log::error('Error in GoogleSearchConsoleService constructor: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getAuthUrl()
    {
        try {
            $authUrl = $this->client->createAuthUrl();
            Log::info('Generated auth URL: ' . $authUrl);
            return $authUrl;
        } catch (\Exception $e) {
            Log::error('Error generating auth URL: ' . $e->getMessage());
            throw $e;
        }
    }

    public function handleCallback($code)
    {
        try {
            Log::info('Handling callback with code: ' . substr($code, 0, 10) . '...');
            $token = $this->client->fetchAccessTokenWithAuthCode($code);
            
            if (!isset($token['error'])) {
                Log::info('Successfully obtained access token');
                Session::put('google_token', $token);
                $this->client->setAccessToken($token);
                return true;
            }
            
            Log::error('Error getting access token: ' . ($token['error'] ?? 'Unknown error'));
            throw new \Exception('Error getting access token: ' . ($token['error'] ?? 'Unknown error'));
        } catch (\Exception $e) {
            Log::error('Exception in handleCallback: ' . $e->getMessage());
            throw $e;
        }
    }

    public function disconnect()
    {
        Session::forget('google_token');
        Log::info('Disconnected from Google Search Console');
        return true;
    }

    public function getSites()
    {
        try {
            if (!$this->client->getAccessToken()) {
                Log::error('Not authenticated with Google');
                throw new \Exception('Not authenticated with Google');
            }

            Log::info('Fetching Search Console sites');
            $siteList = $this->searchConsole->sites->listSites();
            $sites = collect($siteList->getSiteEntry())->map(function($site) {
                return [
                    'siteUrl' => $site->getSiteUrl(),
                    'permissionLevel' => $site->getPermissionLevel()
                ];
            });
            
            Log::info('Successfully fetched ' . $sites->count() . ' sites');
            return $sites;
            
        } catch (\Exception $e) {
            Log::error('Error fetching Search Console sites: ' . $e->getMessage());
            throw $e;
        }
    }

    public function fetchSearchAnalytics($siteUrl, $startDate, $endDate, $dimensions = ['query', 'page'])
    {
        try {
            if (!$this->client->getAccessToken()) {
                Log::error('Not authenticated with Google');
                throw new \Exception('Not authenticated with Google');
            }

            Log::info('Fetching Search Console analytics for site: ' . $siteUrl);
            $request = new SearchAnalyticsQueryRequest();
            $request->setStartDate($startDate);
            $request->setEndDate($endDate);
            $request->setDimensions($dimensions);
            
            $response = $this->searchConsole->searchanalytics->query($siteUrl, $request);
            
            $data = collect($response->getRows())->map(function($row) use ($dimensions) {
                $data = [
                    'clicks' => $row->getClicks(),
                    'impressions' => $row->getImpressions(),
                    'ctr' => $row->getCtr(),
                    'position' => $row->getPosition()
                ];
                
                foreach ($dimensions as $index => $dimension) {
                    $data[$dimension] = $row->getKeys()[$index];
                }
                
                return $data;
            });
            
            Log::info('Successfully fetched analytics data');
            return $data;
            
        } catch (\Exception $e) {
            Log::error('Error fetching Search Console analytics: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getTopPages($siteUrl, $startDate, $endDate)
    {
        return $this->fetchSearchAnalytics($siteUrl, $startDate, $endDate, ['page']);
    }

    public function getTopQueries($siteUrl, $startDate, $endDate)
    {
        return $this->fetchSearchAnalytics($siteUrl, $startDate, $endDate, ['query']);
    }
}