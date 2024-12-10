<?php

namespace App\Jobs;

use App\Models\AnalysisTask;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\DataForSEOService;
use Exception;

class AnalyzeSEOJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $task;
    protected const MAX_CRAWL_PAGES = 50;

    public function __construct(AnalysisTask $task)
    {
        $this->task = $task;
    }

    public function handle(DataForSEOService $dataForSEO): void
    {
        try {
            $this->task->markAsProcessing();
            $analysis = $this->task->websiteAnalysis;

            \Log::info('Starting SEO analysis', ['url' => $analysis->url]);

            // Set up OnPage API task
            $taskData = [
                'target' => $this->getDomain($analysis->url),
                'max_crawl_pages' => self::MAX_CRAWL_PAGES,
                'load_resources' => true,
                'enable_javascript' => true,
                'enable_browser_rendering' => true,
                'enable_xhr' => true,
                'check_spell' => true,
                'calculate_keyword_density' => true,
                'store_raw_html' => true,
                'validate_micromarkup' => true,
                'checks_threshold' => [
                    'high_loading_time' => 3,
                    'large_page_size' => 1048576,
                    'title_too_short' => 30,
                    'title_too_long' => 65,
                    'low_content_rate' => 0.1,
                    'high_content_rate' => 0.9
                ]
            ];

            // Post task to DataForSEO
            $taskResult = $dataForSEO->postOnPageTask($taskData);
            $taskId = $taskResult['id'] ?? null;

            if (!$taskId) {
                throw new Exception('Failed to create OnPage task');
            }

            // Wait for task completion and get results
            $seoData = $this->waitForResults($dataForSEO, $taskId);

            // Process and store results
            $analysisData = $analysis->analysis_data ?? [];
            $analysisData['seo'] = [
                'task_id' => $taskId,
                'summary' => $seoData['summary'] ?? null,
                'crawl_status' => $seoData['crawl_status'] ?? null,
                'pages_info' => $seoData['pages'] ?? null,
                'non_indexable' => $seoData['non_indexable'] ?? null,
                'duplicate_content' => $seoData['duplicate_content'] ?? null,
                'duplicate_tags' => $seoData['duplicate_tags'] ?? null,
                'links_analysis' => $seoData['links'] ?? null,
                'keyword_density' => $seoData['keyword_density'] ?? null,
                'page_metrics' => $seoData['page_metrics'] ?? null,
                'microdata' => $seoData['microdata'] ?? null,
                'core_web_vitals' => $seoData['core_web_vitals'] ?? null,
                'spell_check' => $seoData['spell_check'] ?? null,
                'raw_html_status' => $seoData['raw_html'] ? 'available' : 'not_available'
            ];

            $analysis->update([
                'analysis_data' => $analysisData
            ]);

            // Mark task as completed with results
            $this->task->markAsCompleted([
                'seo_data' => $analysisData['seo']
            ]);

            \Log::info('SEO analysis completed', [
                'url' => $analysis->url,
                'task_id' => $taskId
            ]);

        } catch (Exception $e) {
            \Log::error('SEO analysis failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->task->markAsFailed($e->getMessage());
            throw $e;
        }
    }

    protected function waitForResults(DataForSEOService $dataForSEO, string $taskId): array
    {
        $maxAttempts = 30;
        $attempt = 0;
        $delay = 10;

        while ($attempt < $maxAttempts) {
            $status = $dataForSEO->getOnPageTaskStatus($taskId);
            
            if ($status === 'completed') {
                return $dataForSEO->getOnPageTaskResult($taskId);
            }
            
            if ($status === 'failed') {
                throw new Exception('OnPage task failed');
            }

            $attempt++;
            sleep($delay);
        }

        throw new Exception('OnPage task timeout');
    }

    protected function getDomain(string $url): string
    {
        $domain = parse_url($url, PHP_URL_HOST);
        return preg_replace('/^www\./', '', $domain);
    }
}
