<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalysisTask extends Model
{
    protected $fillable = [
        'website_analysis_id',
        'task_type',
        'status',
        'result',
        'error_message',
    ];

    protected $casts = [
        'result' => 'array',
    ];

    // Task types
    const TYPE_SCREENSHOT = 'screenshot';
    const TYPE_TECHNOLOGY = 'technology';
    const TYPE_COMPETITOR = 'competitor';
    const TYPE_SEO = 'seo';

    // Statuses
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    public function websiteAnalysis(): BelongsTo
    {
        return $this->belongsTo(WebsiteAnalysis::class);
    }

    public function markAsProcessing(): void
    {
        $this->update(['status' => self::STATUS_PROCESSING]);
    }

    public function markAsCompleted(array $result = []): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'result' => $result
        ]);

        // Update parent analysis progress
        $this->websiteAnalysis->incrementCompletedTasks();
    }

    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $error
        ]);

        // Update parent analysis progress
        $this->websiteAnalysis->incrementCompletedTasks();
    }
}
