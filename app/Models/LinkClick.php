<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LinkClick extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'event_type',
        'project_code',
        'link_url',
        'url_code',
        'link_text',
        'click_class',
        'click_id',
        'data_attributes',
        'page_url',
        'click_type',
        'coordinates_x',
        'coordinates_y',
    ];

    // Define the relationship to the Project model
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_code', 'project_code');
    }
}
