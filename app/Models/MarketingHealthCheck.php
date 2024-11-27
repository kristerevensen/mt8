<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Carbon\Carbon;

class MarketingHealthCheck extends Model
{
    use HasFactory;

    protected $fillable = [
        'url',
        'trust_signals',
        'conversion_elements',
        'localization',
        'technical_seo',
        'privacy_compliance',
        'mobile_experience',
        'analytics_setup',
        'total_score',
        'last_checked_at'
    ];

    protected $casts = [
        'trust_signals' => 'array',
        'conversion_elements' => 'array',
        'localization' => 'array',
        'technical_seo' => 'array',
        'privacy_compliance' => 'array',
        'mobile_experience' => 'array',
        'analytics_setup' => 'array',
        'last_checked_at' => 'datetime'
    ];

    protected $appends = ['overall_health', 'formatted_date'];

    public function getOverallHealthAttribute()
    {
        $scores = [
            $this->calculateCategoryScore($this->trust_signals),
            $this->calculateCategoryScore($this->conversion_elements),
            $this->calculateCategoryScore($this->localization),
            $this->calculateCategoryScore($this->technical_seo),
            $this->calculateCategoryScore($this->privacy_compliance),
            $this->calculateCategoryScore($this->mobile_experience),
            $this->calculateCategoryScore($this->analytics_setup)
        ];

        return round(array_sum($scores) / count($scores));
    }

    public function getFormattedDateAttribute()
    {
        return $this->last_checked_at->locale('nb_NO')->diffForHumans();
    }

    private function calculateCategoryScore($category)
    {
        if (!is_array($category)) return 0;

        $totalChecks = 0;
        $passedChecks = 0;

        foreach ($category as $key => $value) {
            if ($key === 'score' || $key === 'details') continue;

            $totalChecks++;
            if ($value === true) $passedChecks++;
        }

        return $totalChecks > 0 ? round(($passedChecks / $totalChecks) * 100) : 0;
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('last_checked_at', 'desc');
    }

    public function scopeForDomain($query, $domain)
    {
        return $query->where('url', 'like', "%{$domain}%");
    }
}
