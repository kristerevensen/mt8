<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversion extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'goal_uuid',
        'page_url',
        'referrer',
        'timestamp',
    ];

    /**
     * Definerer relasjonen til Goal-modellen.
     */
    public function goal()
    {
        return $this->belongsTo(Goal::class, 'goal_uuid', 'goal_uuid');
    }
}
