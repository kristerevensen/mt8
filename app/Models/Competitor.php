<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Competitor extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'target_domain',
        'competitor_domain',
        'se_type',
        'location_code',
        'language_code',
        'avg_position',
        'sum_position',
        'intersections',
        'full_domain_metrics',
        'metrics',
        'competitor_metrics',
    ];

    protected $casts = [
        'full_domain_metrics' => 'array',
        'metrics' => 'array',
        'competitor_metrics' => 'array',
    ];

    // In WebsiteSpyRequestTechnologies.php

    public function competitors()
    {
        return $this->hasMany(Competitor::class, 'uuid', 'uuid');
    }
}
