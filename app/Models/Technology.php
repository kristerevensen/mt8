<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Technology extends Model
{
    protected $fillable = [
        'website_analysis_id',
        'uuid',
        'category',
        'subcategory',
        'name'
    ];

    protected $casts = [
        'category' => 'string',
        'subcategory' => 'string',
        'name' => 'string'
    ];

    // Technology categories from DataForSEO
    const CATEGORY_WEB_DEVELOPMENT = 'web_development';
    const CATEGORY_ADD_ONS = 'add_ons';
    const CATEGORY_SERVERS = 'servers';
    const CATEGORY_CONTENT = 'content';
    const CATEGORY_MEDIA = 'media';
    const CATEGORY_LOCATION = 'location';

    // Common subcategories
    const SUBCATEGORY_JAVASCRIPT_LIBRARIES = 'javascript_libraries';
    const SUBCATEGORY_PROGRAMMING_LANGUAGES = 'programming_languages';
    const SUBCATEGORY_WORDPRESS_PLUGINS = 'wordpress_plugins';
    const SUBCATEGORY_PERFORMANCE = 'performance';
    const SUBCATEGORY_CDN = 'cdn';
    const SUBCATEGORY_DATABASES = 'databases';
    const SUBCATEGORY_CMS = 'cms';
    const SUBCATEGORY_BLOGS = 'blogs';
    const SUBCATEGORY_PHOTO_GALLERIES = 'photo_galleries';
    const SUBCATEGORY_MAPS = 'maps';

    public function analysis(): BelongsTo
    {
        return $this->belongsTo(WebsiteAnalysis::class, 'website_analysis_id');
    }
}
