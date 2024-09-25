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
        'language_name', // Add this field to allow mass assignment
        'language_code', // Add this field to allow mass assignment
    ];

    // You can add additional methods and relationships here if needed

    // set relationship to project, where many projects will have one language
    public function projects()
    {
        return $this->hasMany(Project::class, 'project_language', 'language_code');
    }
}
