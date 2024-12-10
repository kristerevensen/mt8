<?php

namespace App\Jobs;

use App\Models\WebsiteAnalysis;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\DataForSEOService;
use App\Http\Controllers\WebsiteSpyController;
use App\Events\WebsiteAnalysisCompleted;
use App\Events\WebsiteAnalysisFailed;
use Illuminate\Support\Carbon;

class AnalyzeWebsiteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $analysis;

    public function __construct(WebsiteAnalysis $analysis)
    {
        $this->analysis = $analysis;
    }

    public function handle(): void
    {
        try {
            $this->analysis->update(['status' => 'processing']);

            // Get DataForSEO data
            $dataForSEO = app(DataForSEOService::class);
            
            // Get and process technologies
            $techData = $dataForSEO->getTechnologies($this->analysis->url);
            app(WebsiteSpyController::class)->processWebsiteTechnologies($this->analysis, $techData);

            // Get and process historical ranks
            $rankData = $dataForSEO->getHistoricalRanks($this->analysis->url);
            
            // Debug output
            \Log::info('Historical rank data received', [
                'url' => $this->analysis->url,
                'data_count' => count($rankData),
                'sample_data' => array_slice($rankData, 0, 2) // Show first 2 items
            ]);
            
            dd([
                'url' => $this->analysis->url,
                'rank_data' => $rankData,
                'date_from' => Carbon::create(2024, 12, 8)->subYears(3)->format('Y-m-d'),
                'date_to' => Carbon::create(2024, 12, 7)->format('Y-m-d')
            ]);

            app(WebsiteSpyController::class)->processHistoricalRanks($this->analysis, $rankData);

            // Update status to completed
            $this->analysis->update(['status' => 'completed']);

            event(new WebsiteAnalysisCompleted($this->analysis));

        } catch (\Exception $e) {
            \Log::error('Website analysis job failed', [
                'error' => $e->getMessage(),
                'analysis_id' => $this->analysis->id
            ]);

            $this->analysis->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);

            event(new WebsiteAnalysisFailed($this->analysis));

            throw $e;
        }
    }
}