<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class KeywordList extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'project_code', 'list_uuid'];

    protected $primaryKey = 'list_uuid'; // Angi at primary key er 'list_uuid'
    protected $keyType = 'string'; // UUID-er er strenger
    public $incrementing = false; // UUID-er er ikke auto-inkrementerende

    protected static function boot()
    {
        parent::boot();

        // Generer UUID når en ny KeywordList blir opprettet
        static::creating(function ($model) {
            $model->list_uuid = Str::uuid();
        });
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_code', 'project_code');
    }

    // Definer relasjonen mellom KeywordList og Keyword
    public function keywords()
    {
        return $this->hasMany(Keyword::class, 'list_uuid', 'list_uuid');
    }
}
