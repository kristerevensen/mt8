<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeoTask extends Model
{
    use HasFactory;

    /**
     * Tabellen som modellen bruker.
     *
     * @var string
     */
    protected $table = 'seo_tasks';

    /**
     * Attributtene som er mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'project_code',
        'location_name',
        'target',
        'tag',
        'pingback_url',
        'postback_url',
        'status',
        'result',
    ];

    /**
     * Attributtene som bør konverteres til datotyper.
     *
     * @var array
     */
    protected $casts = [
        'result' => 'array',
    ];

    /**
     * Relasjon til prosjektet som oppgaven tilhører.
     */
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_code', 'project_code');
    }
}
