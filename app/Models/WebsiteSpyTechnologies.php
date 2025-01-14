<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebsiteSpyTechnologies extends Model
{
    use HasFactory;

    protected $table = 'website_spy_technologies';

    protected $fillable = [
        'uuid',
        'project_code',
        'website_analysis_id',
        'name',
        'version',
        'icon',
        'website',
        'cpe',
        'categories'
    ];

    protected $casts = [
        'categories' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the website analysis that owns this technology.
     */
    public function websiteAnalysis()
    {
        return $this->belongsTo(WebsiteAnalysis::class, 'website_analysis_id');
    }
}
