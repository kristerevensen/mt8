<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SearchConsoleData extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_code',
        'url',
        'type',
        'dimension_value',
        'clicks',
        'impressions',
        'ctr',
        'position',
        'date'
    ];

    protected $casts = [
        'clicks' => 'integer',
        'impressions' => 'integer',
        'ctr' => 'float',
        'position' => 'float',
        'date' => 'date'
    ];

    /**
     * Get the project that owns this data
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_code', 'project_code');
    }

    /**
     * Scope a query to only include data for a specific project
     */
    public function scopeForProject($query, string $projectCode)
    {
        return $query->where('project_code', $projectCode);
    }

    /**
     * Scope a query to only include data for a specific date range
     */
    public function scopeForDateRange($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope a query to only include data for a specific type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get the top performing items for a specific type
     */
    public static function getTopItems(string $projectCode, string $type, string $startDate, string $endDate, int $limit = 100)
    {
        return static::query()
            ->forProject($projectCode)
            ->ofType($type)
            ->forDateRange($startDate, $endDate)
            ->orderByDesc('clicks')
            ->limit($limit)
            ->get();
    }
}
