<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocationLanguage extends Model
{
    use HasFactory;

    protected $table = 'location_language';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'location_code',
        'language_code',
        'location_name',
        'country_iso_code',
        'language_name',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'location_code' => 'integer',
    ];

    // Relationship to Location
    public function location()
    {
        return $this->belongsTo(Location::class, 'location_code', 'location_code');
    }

    // Relationship to Language
    public function language()
    {
        return $this->belongsTo(Language::class, 'language_code', 'language_code');
    }
}
