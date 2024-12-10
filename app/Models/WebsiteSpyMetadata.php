<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebsiteSpyMetadata extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'project_code',
        'website_analysis_id',
        'title',
        'description',
        'meta_keywords',
        'phone_numbers',
        'email_addresses',
        'social_media_urls',
        'country_iso_code',
        'language_code'
    ];

    protected $casts = [
        'phone_numbers' => 'array',
        'email_addresses' => 'array',
        'social_media_urls' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function analysis()
    {
        return $this->belongsTo(WebsiteAnalysis::class, 'website_analysis_id');
    }
}
