<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Exception;
use Carbon\Carbon;

class DataForSEOService
{
    private $login;
    private $password;
    private $apiUrl = 'https://api.dataforseo.com';

    public function __construct()
    {
        $this->login = config('services.dataforseo.login');
        $this->password = config('services.dataforseo.password');
    }

    public function getTechnologies(string $target): array
    {
        try {
            $http = Http::withBasicAuth($this->login, $this->password);
            
            // Skip SSL verification in development
            if (app()->environment('local')) {
                $http->withoutVerifying();
            }
            
            $response = $http->post("{$this->apiUrl}/v3/domain_analytics/technologies/domain_technologies/live", [[
                'target' => $target,
                'max_crawl_pages' => 1,
                'load_resources' => false,
                'enable_javascript' => false,
                'enable_browser_rendering' => false,
            ]]);


            if (!$response->successful()) {
                throw new Exception('Failed to get technologies: ' . $response->body());
            }

            $result = $response->json();
            
            if (!empty($result['tasks'])) {
                return $result['tasks'][0]['result'] ?? [];
            }
        } catch (Exception $e) {
            \Log::error('DataForSEO API Error: ' . $e->getMessage());
        }

        return [];
    }

    public function getHistoricalRanks(string $url, ?string $locationCode = null, ?string $languageCode = null): array
    {
        try {
            $http = Http::withBasicAuth($this->login, $this->password);
            
            // Skip SSL verification in development
            if (app()->environment('local')) {
                $http->withoutVerifying();
            }

            // Get data for the last 3 years until yesterday
            $dateFrom = Carbon::now()->subYears(3)->format('Y-m-d');
            $dateTo = Carbon::now()->subDay()->format('Y-m-d'); // yesterday
            
            $requestData = [
                'target' => $this->getDomain($url),
                'language_code' => $languageCode ?? 'en',
                'location_code' => $locationCode ? (int)$locationCode : 2840,
                'date_from' => $dateFrom,
                'date_to' => $dateTo
            ];
            
            \Log::info('DataForSEO Historical Ranks Request', $requestData);
            
            $response = $http->post("{$this->apiUrl}/v3/dataforseo_labs/google/historical_rank_overview/live", [$requestData]);
            

            \Log::info('DataForSEO Historical Ranks Response', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);

            if (!$response->successful()) {
                throw new Exception('Failed to get historical ranks: ' . $response->body());
            }

            $data = $response->json();
            
            if (empty($data['tasks'][0]['result'][0]['items'])) {
                \Log::warning('No historical rank items found', [
                    'url' => $url,
                    'response' => $data
                ]);
                return [];
            }

            return $data['tasks'][0]['result'][0]['items'];

        } catch (Exception $e) {
            \Log::error('DataForSEO historical rank detection failed', [
                'error' => $e->getMessage(),
                'url' => $url
            ]);
            throw $e;
        }
    }

    public function postOnPageTask(array $params): string
    {
        $response = Http::withBasicAuth($this->login, $this->password)
            ->post("{$this->apiUrl}/on_page/task_post", [$params]);

        if (!$response->successful()) {
            throw new Exception('Failed to create DataForSEO task: ' . $response->body());
        }

        $data = $response->json();
        if (!isset($data['tasks'][0]['id'])) {
            throw new Exception('No task ID in DataForSEO response');
        }

        return $data['tasks'][0]['id'];
    }

    public function getTaskResult(string $taskId): array
    {
        $response = Http::withBasicAuth($this->login, $this->password)
            ->get("{$this->apiUrl}/on_page/pages", [
                'id' => $taskId
            ]);

        if (!$response->successful()) {
            throw new Exception('Failed to get task result: ' . $response->body());
        }

        $data = $response->json();
        if (!isset($data['tasks'][0]['result'])) {
            throw new Exception('No result in task response');
        }

        return $data['tasks'][0]['result'];
    }

    protected function waitForTaskCompletion(string $taskId, int $maxAttempts = 30, int $delay = 10): array
    {
        $attempts = 0;
        while ($attempts < $maxAttempts) {
            $status = $this->getTaskStatus($taskId);
            
            if ($status === 'completed') {
                return $this->getTaskResult($taskId);
            }
            
            if ($status === 'failed') {
                throw new Exception('DataForSEO task failed');
            }

            $attempts++;
            sleep($delay);
        }

        throw new Exception('DataForSEO task timeout');
    }

    protected function getTaskStatus(string $taskId): string
    {
        $response = Http::withBasicAuth($this->login, $this->password)
            ->get("{$this->apiUrl}/on_page/tasks_ready", [
                'id' => $taskId
            ]);

        if (!$response->successful()) {
            throw new Exception('Failed to get task status: ' . $response->body());
        }

        $data = $response->json();
        return $data['tasks'][0]['status_message'] === 'Ok.' ? 'completed' : 'pending';
    }

    protected function getDomain(string $url): string
    {
        // Remove protocol
        $domain = preg_replace('(^https?://)', '', $url);
        // Remove path
        $domain = explode('/', $domain)[0];
        // Remove www
        $domain = preg_replace('/^www\./', '', $domain);
        return $domain;
    }

    /**
     * Get ranked keywords for a domain from DataForSEO
     */
    public function getRankedKeywords(string $target, int $locationCode, string $languageCode): array
    {
        try {
            $http = Http::withBasicAuth($this->login, $this->password);
            
            // Skip SSL verification in development
            if (app()->environment('local')) {
                $http->withoutVerifying();
            }
            
            // Clean target by removing https:// and www.
            $target = str_replace(['https://', 'http://', 'www.'], '', $target);
            
            $response = $http->post("{$this->apiUrl}/v3/dataforseo_labs/google/ranked_keywords/live", [[
                'target' => $target,
                'location_code' => $locationCode,
                'language_code' => $languageCode,
                'limit' => 100,
                'item_types' => ["organic", "paid", "featured_snippet", "local_pack"],
                'load_rank_absolute' => true
            ]]);

            if (!$response->successful()) {
                throw new Exception('Failed to get ranked keywords: ' . $response->body());
            }

            $result = $response->json();
            
            if (!empty($result['tasks'])) {
                return $result['tasks'];
            }

            return [];
        } catch (Exception $e) {
            \Log::error('DataForSEO API Error (Ranked Keywords): ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get competitors for a domain from DataForSEO
     */
    public function getCompetitors(string $target, int $locationCode, string $languageCode): array
    {
        try {
            $http = Http::withBasicAuth($this->login, $this->password);
            
            // Skip SSL verification in development
            if (app()->environment('local')) {
                $http->withoutVerifying();
            }
            
            // Clean target by removing https:// and www.
            $target = $this->getDomain($target);
            
            $response = $http->post("{$this->apiUrl}/v3/dataforseo_labs/google/competitors_domain/live", [[
                'target' => $target,
                'location_code' => $locationCode,
                'language_code' => $languageCode,
                'exclude_top_domains' => true,
                'limit' => 100
            ]]);

            if (!$response->successful()) {
                throw new Exception('Failed to get competitors: ' . $response->body());
            }

            $result = $response->json();
            
            if (!empty($result['tasks'][0]['result'][0]['items'])) {
                return $result['tasks'][0]['result'][0]['items'];
            }

            return [];
        } catch (Exception $e) {
            \Log::error('DataForSEO API Error (Competitors): ' . $e->getMessage(), [
                'target' => $target,
                'location_code' => $locationCode,
                'language_code' => $languageCode
            ]);
            throw $e;
        }
    }
}
