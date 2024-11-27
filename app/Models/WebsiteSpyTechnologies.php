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
        'status_message',
        'status_code',
        'time',
        'category',
        'subcategory',
        'item_title',
        'description',
    ];

    // Relasjon til Project-modellen
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_code', 'project_code');
    }
}
