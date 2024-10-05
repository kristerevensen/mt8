<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    // Define table name
    protected $table = 'projects';

    // Mass assignable attributes
    protected $fillable = [
        'project_code',
        'project_name',
        'project_domain',
        'project_language',
        'project_country',
        'project_category',
        'owner_id',
        'team_id',
    ];

    // Casts to array for project_category (JSON column)
    protected $casts = [
        'project_category' => 'array',
    ];

    // Define primary key for the model
    protected $primaryKey = 'project_code';

    // Indicate that the primary key is not auto-incrementing
    public $incrementing = false;

    // Set the primary key type as string (since it's UUID or a custom string)
    protected $keyType = 'string';

    /**
     * Relationships
     */

    // Relationship to SEO tasks
    public function seoTasks()
    {
        return $this->hasMany(SeoTask::class, 'project_code', 'project_code');
    }

    // Relationship to the project owner (User model)
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    // Relationship to the team that owns the project
    public function team()
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    // Relationship to campaigns
    public function campaigns()
    {
        return $this->hasMany(Campaign::class, 'project_code', 'project_code');
    }

    // Relationship to link clicks
    public function linkClicks()
    {
        return $this->hasMany(LinkClick::class, 'project_code', 'project_code');
    }

    // Relationship to goals
    public function goals()
    {
        return $this->hasMany(Goal::class, 'project_code', 'project_code');
    }

    // Relationship to keyword lists
    public function keywordLists()
    {
        return $this->hasMany(KeywordList::class, 'project_code', 'project_code');
    }

    // Relationship to keywords
    public function keywords()
    {
        return $this->hasMany(Keyword::class, 'project_code', 'project_code');
    }

    // Relationship to keyword search volumes
    public function keywordSearchVolumes()
    {
        return $this->hasManyThrough(
            KeywordSearchVolume::class,
            Keyword::class,
            'project_code',  // Foreign key on the keywords table
            'keyword_uuid',  // Foreign key on the keyword_search_volumes table
            'project_code',  // Local key on the projects table
            'keyword_uuid'   // Local key on the keywords table
        );
    }

    // relationship to locations
    public function location()
    {
        return $this->belongsTo(Location::class, 'project_location_code', 'location_code');
    }

    //set up relationship to language
    public function language()
    {
        return $this->belongsTo(Language::class, 'project_language', 'language_code');
    }

    //set up relationship to optimization categories
    public function optimizationCategories()
    {
        return $this->hasMany(OptimizationCategory::class, 'project_code', 'project_code');
    }

    //set up relationship to optimization criteria
    public function optimizationCriterias()
    {
        return $this->hasMany(OptimizationCriteria::class, 'project_code', 'project_code');
    }

    //set up relationship to criteria solutions
    public function criteriaSolutions()
    {
        return $this->hasMany(CriteriaSolution::class, 'project_code', 'project_code');
    }

}
