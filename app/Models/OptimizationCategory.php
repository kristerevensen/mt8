<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OptimizationCategory extends Model
{
    use HasFactory;

    protected $table = 'optimization_categories'; // Sett riktig tabellnavn hvis det ikke følger standarden

    protected $fillable = [
        'category_name',
        'category_uuid',
        'project_code',
    ];

    // Relasjon: En kategori har mange kriterier
    public function criterias()
    {
        return $this->hasMany(OptimizationCriteria::class, 'category_uuid', 'category_uuid');
    }
}
