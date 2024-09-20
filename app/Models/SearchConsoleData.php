<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SearchConsoleData extends Model
{
    use HasFactory;

    protected $fillable = ['url', 'clicks', 'impressions', 'date', 'project_code'];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_code', 'code');
    }
}
