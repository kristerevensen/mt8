<?php

namespace App\Jobs;

use App\Models\AnalysisTask;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Process\Process;
use Exception;

class AnalyzeScreenshotJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $task;

    public function __construct(AnalysisTask $task)
    {
        $this->task = $task;
    }

    public function handle(): void
    {
        try {
            $this->task->markAsProcessing();
            $analysis = $this->task->websiteAnalysis;

            \Log::info('Starting screenshot process', ['url' => $analysis->url]);
            
            $storageDir = storage_path('app/public/screenshots');
            
            // Ensure directory exists
            if (!file_exists($storageDir)) {
                \Log::info('Creating screenshots directory', ['path' => $storageDir]);
                mkdir($storageDir, 0777, true);
            }

            // Define paths for both desktop and mobile screenshots
            $desktopPath = "{$storageDir}/{$analysis->uuid}_desktop.jpg";
            $mobilePath = "{$storageDir}/{$analysis->uuid}_mobile.jpg";

            // Take desktop screenshot
            $this->takeScreenshot($analysis->url, $desktopPath, 'desktop');
            
            // Take mobile screenshot
            $this->takeScreenshot($analysis->url, $mobilePath, 'mobile');

            // Update paths in analysis
            $analysis->update([
                'screenshot_path' => "screenshots/{$analysis->uuid}_desktop.jpg",
                'screenshot_mobile_path' => "screenshots/{$analysis->uuid}_mobile.jpg"
            ]);

            // Mark task as completed with results
            $this->task->markAsCompleted([
                'desktop_path' => $analysis->screenshot_path,
                'mobile_path' => $analysis->screenshot_mobile_path
            ]);

            \Log::info('Screenshot process completed', [
                'desktop' => $desktopPath,
                'mobile' => $mobilePath
            ]);

        } catch (Exception $e) {
            \Log::error('Screenshot process failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->task->markAsFailed($e->getMessage());
            throw $e;
        }
    }

    protected function takeScreenshot(string $url, string $path, string $type): void
    {
        $process = new Process([
            'node',
            base_path('resources/js/screenshot.js'),
            $url,
            $path,
            $type
        ]);
        
        $process->setTimeout(30);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new Exception("Failed to take {$type} screenshot: " . $process->getErrorOutput());
        }

        if (!file_exists($path)) {
            throw new Exception("Screenshot file not created at {$path}");
        }

        if (filesize($path) === 0) {
            throw new Exception("Screenshot file is empty at {$path}");
        }
    }
}
