<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CriteriaSolution extends Model
{
    use HasFactory;

    protected $fillable = [
        'solution_name',
        'solution_description',
        'criteria_uuid',
        'project_code',
    ];

    // belongs to criteria
    public function criteria()
    {
        return $this->belongsTo(OptimizationCriteria::class, 'criteria_uuid', 'criteria_uuid');
    }

    // belongs to project
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_code', 'project_code');
    }


}
