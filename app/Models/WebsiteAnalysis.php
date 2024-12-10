<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use App\Models\WebsiteSpyTechnologies;
use App\Models\Competitor;
use App\Models\Project;
use App\Models\AnalysisTask;
use App\Models\WebsiteHistoricalRank;
use App\Models\WebsiteSpyMetadata;
use App\Models\WebsiteKeyword;

class WebsiteAnalysis extends Model
{
    use HasFactory;

    protected $table = 'website_analyses';

    protected $fillable = [
        'uuid',
        'project_code',
        'url',
        'location_code',
        'language_code',
        'title',
        'description',
        'meta_keywords',
        'domain_rank',
        'last_visited',
        'country_iso_code',
        'content_language_code',
        'phone_numbers',
        'emails',
        'social_graph_urls',
        'status',
        'error_message',
        'total_tasks',
        'completed_tasks',
        'last_task_completed_at',
        'new_column_1',
        'new_column_2',
    ];

    protected $casts = [
        'meta_keywords' => 'array',
        'phone_numbers' => 'array',
        'emails' => 'array',
        'social_graph_urls' => 'array',
        'location_code' => 'integer',
        'language_code' => 'string',
        'last_visited' => 'datetime'
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // When deleting a website analysis, also delete related records
        static::deleting(function ($analysis) {
            $analysis->technologies()->delete();
            $analysis->competitors()->delete();
            $analysis->tasks()->delete();

            // Delete screenshots if they exist
            if ($analysis->screenshot_path) {
                \Storage::disk('public')->delete($analysis->screenshot_path);
            }
            if ($analysis->mobile_screenshot_path) {
                \Storage::disk('public')->delete($analysis->mobile_screenshot_path);
            }
        });
    }

    /**
     * Get the technologies associated with this analysis.
     */
    public function technologies()
    {
        return $this->hasMany(WebsiteSpyTechnologies::class, 'uuid', 'uuid');
    }

    /**
     * Get the competitors associated with this analysis.
     */
    public function competitors()
    {
        return $this->hasMany(Competitor::class, 'uuid', 'uuid');
    }

    /**
     * Get the project that owns this analysis.
     */
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_code', 'project_code');
    }

    /**
     * Get the tasks associated with this analysis.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(AnalysisTask::class);
    }

    /**
     * Get the historical ranks associated with this analysis.
     */
    public function historicalRanks()
    {
        return $this->hasMany(WebsiteHistoricalRank::class, 'website_analysis_id');
    }

    /**
     * Get the metadata associated with this analysis.
     */
    public function metadata()
    {
        return $this->hasOne(WebsiteSpyMetadata::class, 'website_analysis_id');
    }

    /**
     * Get the keywords associated with this analysis.
     */
    public function keywords()
    {
        return $this->hasMany(WebsiteKeyword::class);
    }

    public function initializeTasks(): void
    {
        DB::transaction(function () {
            // Create only the technology task
            $tasks = [
                ['task_type' => AnalysisTask::TYPE_TECHNOLOGY],
            ];

            foreach ($tasks as $task) {
                $this->tasks()->create($task);
            }

            // Update total tasks count
            $this->update([
                'total_tasks' => count($tasks),
                'completed_tasks' => 0
            ]);
        });
    }

    public function incrementCompletedTasks(): void
    {
        DB::transaction(function () {
            $this->increment('completed_tasks');
            $this->update(['last_task_completed_at' => now()]);

            // Check if all tasks are completed
            if ($this->completed_tasks >= $this->total_tasks) {
                $this->update(['status' => 'completed']);
            }
        });
    }

    public function getProgress(): array
    {
        $tasks = $this->tasks()->get();
        
        return [
            'total' => $this->total_tasks,
            'completed' => $this->completed_tasks,
            'tasks' => $tasks->map(function ($task) {
                return [
                    'type' => $task->task_type,
                    'status' => $task->status,
                    'error' => $task->error_message
                ];
            })
        ];
    }
}
