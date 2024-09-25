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
        'country_iso_code', // Add this field to allow mass assignment
        'location_code', // Add this field to allow mass assignment
        'location_code_parent', // Add this field to allow mass assignment
        'location_type', // Add this field to allow mass assignment
    ];

    // You can add additional methods and relationships here if needed


    // set relationship to project, where many projects will have one location
    public function projects()
    {
        return $this->hasMany(Project::class, 'project_location_code', 'location_code');
    }
}
