<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'language_name',
        'language_code',
    ];

    /**
     * The locations that belong to the language.
     */
    public function locations()
    {
        return $this->belongsToMany(Location::class, 'language_location', 'language_code', 'location_code')
                    ->withPivot(['location_name', 'country_iso_code', 'language_name']);
    }

    /**
     * The projects that belong to the language.
     */
    public function projects()
    {
        return $this->hasMany(Project::class, 'project_language', 'language_code');
    }

    /**
     * The analyses that belong to the language.
     */
    public function analyses()
    {
        return $this->hasMany(WebsiteAnalysis::class, 'language_code', 'language_code');
    }
}
