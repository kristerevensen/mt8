<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OptimizationCriteria extends Model
{
    use HasFactory;

    protected $table = 'optimization_criteria'; // Sett riktig tabellnavn hvis det ikke følger standarden

    protected $fillable = [
        'criteria_name',
        'criteria_description',
        'criteria_uuid',
        'category_uuid', // Legg til dette feltet for relasjonen til kategori
        'project_code',
    ];

    // Relasjon: Et kriterium tilhører en kategori
    public function category()
    {
        return $this->belongsTo(OptimizationCategory::class, 'category_uuid', 'category_uuid');
    }

    // Relasjon: Et kriterium har mange løsninger
    public function solutions()
    {
        return $this->hasMany(CriteriaSolution::class, 'criteria_uuid', 'criteria_uuid');
    }

    // belongs to project
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_code', 'project_code');
    }
}
