<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'project_code',
        'form_id',
        'form_name',
        'page_url',
        'form_data',
    ];

    /**
     * Definerer relasjonen til Project-modellen.
     */
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_code', 'project_code');
    }
}
