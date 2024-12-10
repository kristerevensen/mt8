<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'location_name',
        'country_iso_code',
        'location_code',
        'location_code_parent',
        'location_type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'location_code' => 'integer',
    ];

    /**
     * The languages that belong to the location.
     */
    public function languages()
    {
        return $this->belongsToMany(Language::class, 'language_location', 'location_code', 'language_code')
                    ->withPivot(['location_name', 'country_iso_code', 'language_name']);
    }

    /**
     * The projects that belong to the location.
     */
    public function projects()
    {
        return $this->hasMany(Project::class, 'project_location_code', 'location_code');
    }

    /**
     * The analyses that belong to the location.
     */
    public function analyses()
    {
        return $this->hasMany(WebsiteAnalysis::class, 'location_code', 'location_code');
    }
}
