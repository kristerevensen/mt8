<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataPage extends Model
{
    use HasFactory;

    // Specify the table name if it's not the plural form of the model name
    protected $table = 'data_pages';

    // Specify the attributes that are mass assignable
    protected $fillable = [
        'url',
        'url_code',
        'event_type',
        'title',
        'referrer',
        'device_type',
        'session_start',
        'project_code',
        'session_id',
        'hostname',
        'protocol',
        'pathname',
        'language',
        'bounce',
        'entrance',
        'exits',
        'meta_description',
        'cookie_enabled',
        'screen_width',
        'screen_height',
        'history_length',
        'word_count',
        'form_count',
        'inbound_links',
        'outbound_links',
        'analyzed',
        'analyzed_at',
    ];

    // Specify the data types for specific columns
    protected $casts = [
        'cookie_enabled' => 'boolean',
        'analyzed' => 'boolean',
        'analyzed_at' => 'datetime',
    ];

    /**
     * Define a relationship with the Project model
     */
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_code', 'project_code');
    }

    public static function countIncomingLinks($urlCode, $projectCode)
    {
        $dataPage = self::where('url_code', $urlCode)
            ->where('project_code', $projectCode)
            ->first();

        if (!$dataPage || is_null($dataPage->inbound_links)) {
            return 0;
        }

        // Deserialize the inbound_links data
        $inboundLinks = unserialize($dataPage->inbound_links);

        return is_array($inboundLinks) ? count($inboundLinks) : 0;
    }
}
