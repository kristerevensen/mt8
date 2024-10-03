<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Keyword extends Model
{
    use HasFactory;

    // Define the table associated with the model
    protected $table = 'keywords';

    // Define which attributes can be mass-assigned
    protected $fillable = [
        'keyword_uuid',
        'keyword', // The actual keyword
        'project_code',
        'spell',
        'location_code',
        'language_code',
        'search_partners',
        'competition',
        'competition_index',
        'search_volume',
        'low_top_of_page_bid',
        'high_top_of_page_bid',
        'cpc',
        'analyzed_at', // Timestamp when the keyword was analyzed
    ];

    // Define the primary key for the table
    protected $primaryKey = 'keyword_uuid';

    // If the primary key is not an incrementing integer, set this to false
    public $incrementing = false;

    // The "type" of the auto-incrementing ID
    protected $keyType = 'string';

    // Cast the analyzed_at field to a datetime object
    protected $casts = [
        'analyzed_at' => 'datetime',
    ];

    /**
     * Relationships
     */

    // Relationship: A keyword belongs to a project
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_code', 'project_code');
    }

    // Relationship: A keyword has many search volumes
    public function searchVolumes()
    {
        return $this->hasMany(KeywordSearchVolume::class, 'keyword_uuid', 'keyword_uuid');
    }

    /**
     * Methods for easier data handling
     */

    // Method to check if the keyword is analyzed
    public function isAnalyzed()
    {
        return !is_null($this->analyzed_at);
    }

    // Keyword.php
    public function monthlySearchVolumes()
    {
        return $this->hasMany(KeywordSearchVolume::class, 'keyword_uuid', 'keyword_uuid');
    }
}
