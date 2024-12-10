<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\WebsiteAnalysis;
use App\Models\Project;

class Competitor extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'project_code',
        'target_domain',
        'competitor_domain',
        'se_type',
        'location_code',
        'language_code',
        'avg_position',
        'sum_position',
        'intersections',
        'full_domain_metrics',
        'metrics',
        'competitor_metrics',
        'website_analysis_id'
    ];

    protected $casts = [
        'full_domain_metrics' => 'array',
        'metrics' => 'array',
        'competitor_metrics' => 'array',
        'avg_position' => 'float',
        'sum_position' => 'float',
        'intersections' => 'integer'
    ];

    // In WebsiteSpyRequestTechnologies.php

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_code', 'project_code');
    }

    /**
     * Get the website analysis that owns this competitor.
     */
    public function websiteAnalysis()
    {
        return $this->belongsTo(WebsiteAnalysis::class);
    }
}
