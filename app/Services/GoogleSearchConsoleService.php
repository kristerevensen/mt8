<?php

namespace App\Services;

use Google\Client;
use Google\Service\SearchConsole;
use Google\Service\SearchConsole\SearchAnalyticsQueryRequest;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;

class GoogleSearchConsoleService
{
    protected $client;
    protected $searchConsole;
    protected $cachePrefix = 'gsc_';
    protected $cacheDuration = 3600; // 1 hour

    public function __construct()
    {
        try {
            $this->client = new Client();
            $this->client->setApplicationName(Config::get('services.google.application_name'));
            
            // Set OAuth 2.0 credentials from config
            $this->client->setClientId(Config::get('services.google.client_id'));
            $this->client->setClientSecret(Config::get('services.google.client_secret'));
            $this->client->setRedirectUri(Config::get('services.google.redirect'));
            
            // Set OAuth 2.0 access type to offline to get refresh token
            $this->client->setAccessType('offline');
            $this->client->setPrompt('consent');
            $this->client->setIncludeGrantedScopes(true);
            
            // Set the required scope
            $this->client->addScope(SearchConsole::WEBMASTERS_READONLY);
            
        } catch (Exception $e) {
            Log::error('Failed to initialize Google Search Console service', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function getAuthUrl(): string
    {
        return $this->client->createAuthUrl();
    }

    public function handleCallback(string $code): void
    {
        try {
            // Exchange authorization code for access token
            $accessToken = $this->client->fetchAccessTokenWithAuthCode($code);
            
            if (isset($accessToken['error'])) {
                throw new Exception('Error fetching access token: ' . $accessToken['error']);
            }

            // Store the token
            $this->storeToken($accessToken);

            // Initialize SearchConsole service with the new token
            $this->searchConsole = new SearchConsole($this->client);

        } catch (Exception $e) {
            Log::error('Failed to handle OAuth callback', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    protected function storeToken(array $token): void
    {
        Storage::disk('local')->put('google/oauth-token.json', json_encode($token));
    }

    protected function loadToken(): ?array
    {
        if (Storage::disk('local')->exists('google/oauth-token.json')) {
            return json_decode(Storage::disk('local')->get('google/oauth-token.json'), true);
        }
        return null;
    }

    protected function initializeSearchConsole(): void
    {
        if (!$this->searchConsole) {
            $token = $this->loadToken();
            if ($token) {
                $this->client->setAccessToken($token);
                
                // Refresh token if expired
                if ($this->client->isAccessTokenExpired()) {
                    $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
                    $this->storeToken($this->client->getAccessToken());
                }
                
                $this->searchConsole = new SearchConsole($this->client);
            } else {
                throw new Exception('No OAuth token available');
            }
        }
    }

    public function getSites(): array
    {
        try {
            $this->initializeSearchConsole();
            
            $cacheKey = $this->cachePrefix . 'sites';
            
            return Cache::remember($cacheKey, $this->cacheDuration, function () {
                $response = $this->searchConsole->sites->listSites();
                $siteEntries = $response->getSiteEntries();
                
                return array_map(function ($site) {
                    return [
                        'siteUrl' => $site->getSiteUrl(),
                        'permissionLevel' => $site->getPermissionLevel()
                    ];
                }, $siteEntries);
            });
        } catch (Exception $e) {
            Log::error('Failed to get Search Console sites', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function fetchData(string $projectCode, int $months, array $dimensions): void
    {
        try {
            $this->initializeSearchConsole();
            
            // Get sites first
            $sites = $this->getSites();
            
            if (empty($sites)) {
                throw new Exception('No sites available in Search Console');
            }

            $endDate = Carbon::now()->subDay();
            $startDate = Carbon::now()->subMonths($months);

            $dimensionTypes = [];
            if ($dimensions['queries']) $dimensionTypes[] = 'query';
            if ($dimensions['pages']) $dimensionTypes[] = 'page';
            if ($dimensions['countries']) $dimensionTypes[] = 'country';
            if ($dimensions['devices']) $dimensionTypes[] = 'device';

            foreach ($sites as $site) {
                $data = $this->getSearchAnalytics(
                    $site['siteUrl'],
                    $startDate->format('Y-m-d'),
                    $endDate->format('Y-m-d'),
                    $dimensionTypes
                );

                // TODO: Save data to database
                // This will depend on your database structure
                // You might want to use a queue job for this
            }

        } catch (Exception $e) {
            Log::error('Failed to fetch Search Console data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'project_code' => $projectCode
            ]);
            throw $e;
        }
    }

    public function getSearchAnalytics(string $siteUrl, string $startDate, string $endDate, array $dimensions = ['query']): array
    {
        try {
            $this->initializeSearchConsole();
            
            $cacheKey = $this->cachePrefix . md5($siteUrl . $startDate . $endDate . implode(',', $dimensions));
            
            return Cache::remember($cacheKey, $this->cacheDuration, function () use ($siteUrl, $startDate, $endDate, $dimensions) {
                $request = new SearchAnalyticsQueryRequest();
                $request->setStartDate($startDate);
                $request->setEndDate($endDate);
                $request->setDimensions($dimensions);
                $request->setRowLimit(5000);
                
                $response = $this->searchConsole->searchanalytics->query($siteUrl, $request);
                $rows = $response->getRows() ?? [];
                
                return array_map(function ($row) use ($dimensions) {
                    $result = [
                        'clicks' => $row->getClicks(),
                        'impressions' => $row->getImpressions(),
                        'ctr' => $row->getCtr(),
                        'position' => $row->getPosition()
                    ];
                    
                    foreach ($row->getKeys() as $index => $value) {
                        $result[$dimensions[$index]] = $value;
                    }
                    
                    return $result;
                }, $rows);
            });
        } catch (Exception $e) {
            Log::error('Failed to get Search Console analytics', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}