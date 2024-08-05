<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    // define table name
    protected $table = 'projects';
    // Mass assignable attributes
    protected $fillable = [
        'project_code',
        'project_name',
        'project_domain',
        'project_language',
        'project_country',
        'project_category',
        'owner_id',
        'team_id',
    ];

    /**
     * Attributtene som bør konverteres til datotyper.
     *
     * @var array
     */
    protected $casts = [
        'project_category' => 'array',
    ];

    /**
     * Primærnøkkel for modellen.
     *
     * @var string
     */
    protected $primaryKey = 'project_code';

    /**
     * Inkluderer ikke autoincrement.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Tabellen som modellen bruker.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Relasjon til SEO-oppgaver.
     */
    public function seoTasks()
    {
        return $this->hasMany(SeoTask::class, 'project_code', 'project_code');
    }

    // Define relationships
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function team()
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    // Define the relationship with campaigns
    public function campaigns()
    {
        return $this->hasMany(Campaign::class, 'project_code', 'project_code');
    }
}
