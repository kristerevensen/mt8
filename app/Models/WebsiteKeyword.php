<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebsiteKeyword extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'project_code',
        'website_analysis_id',
        'location_code',
        'language_code',
        'keyword',
        'se_type',
        'rank_group',
        'rank_absolute',
        'position',
        'type',
        'is_featured_snippet',
        'search_volume',
        'competition',
        'competition_level',
        'cpc',
        'monthly_searches',
        'traffic_cost'
    ];

    protected $casts = [
        'monthly_searches' => 'array',
        'is_featured_snippet' => 'boolean',
        'competition' => 'float',
        'cpc' => 'float',
        'traffic_cost' => 'float'
    ];

    public function websiteAnalysis()
    {
        return $this->belongsTo(WebsiteAnalysis::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_code', 'project_code');
    }
}
