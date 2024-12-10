<?php

namespace App\Jobs;

use App\Models\AnalysisTask;
use App\Models\WebsiteAnalysis;
use App\Models\WebsiteSpyTechnologies;
use App\Services\DataForSEOService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class AnalyzeTechnologyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $analysis;
    protected $task;
    protected $dataForSEOService;

    public function __construct(AnalysisTask $task)
    {
        $this->task = $task;
        $this->analysis = $task->websiteAnalysis;
    }

    public function handle()
    {
        try {
            $this->task->markAsProcessing();

            $this->dataForSEOService = app(DataForSEOService::class);
            $technologies = $this->dataForSEOService->getTechnologies($this->analysis->url);

            foreach ($technologies as $tech) {
                WebsiteSpyTechnologies::create([
                    'uuid' => $this->analysis->uuid,
                    'project_code' => $this->analysis->project_code,
                    'website_analysis_id' => $this->analysis->id,
                    'name' => $tech['name'],
                    'version' => $tech['version'] ?? null,
                    'icon' => $tech['icon'] ?? null,
                    'website' => $tech['website'] ?? null,
                    'cpe' => $tech['cpe'] ?? null,
                    'categories' => $tech['categories'] ?? [],
                ]);
            }

            $this->task->markAsCompleted(['technologies_count' => count($technologies)]);

        } catch (\Exception $e) {
            \Log::error('Technology analysis failed', [
                'analysis_id' => $this->analysis->id,
                'error' => $e->getMessage()
            ]);

            $this->task->markAsFailed($e->getMessage());
        }
    }
}
