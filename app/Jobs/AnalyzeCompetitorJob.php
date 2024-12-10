<?php

namespace App\Jobs;

use App\Models\AnalysisTask;
use App\Models\Competitor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\DataForSEOService;
use Exception;

class AnalyzeCompetitorJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $task;

    public function __construct(AnalysisTask $task)
    {
        $this->task = $task;
    }

    public function handle(DataForSEOService $dataForSEO): void
    {
        try {
            $this->task->markAsProcessing();
            $analysis = $this->task->websiteAnalysis;

            \Log::info('Starting competitor analysis', ['url' => $analysis->url]);

            // Get competitors from DataForSEO
            $competitors = $dataForSEO->getCompetitors($analysis->url);

            // Store each competitor
            foreach ($competitors as $comp) {
                Competitor::create([
                    'website_analysis_id' => $analysis->id,
                    'url' => $comp['url'],
                    'similarity_score' => $comp['similarity_score'] ?? 0,
                    'rank' => $comp['rank'] ?? null,
                    'traffic' => $comp['traffic'] ?? null,
                ]);
            }

            // Mark task as completed with results
            $this->task->markAsCompleted([
                'competitors_count' => count($competitors),
                'competitors' => $competitors
            ]);

            \Log::info('Competitor analysis completed', [
                'url' => $analysis->url,
                'competitors_count' => count($competitors)
            ]);

        } catch (Exception $e) {
            \Log::error('Competitor analysis failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->task->markAsFailed($e->getMessage());
            throw $e;
        }
    }
}
