<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KeywordSearchVolume extends Model
{
    use HasFactory;

    /**
     * Define the table name
     */
    protected $table = 'keyword_search_volumes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'keyword_uuid',
        'project_code',
        'year',
        'month',
        'search_volume',
    ];

    /**
     * Indicate if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The data type of the primary key.
     *
     * @var string
     */
    protected $keyType = 'int';

    /**
     * Relationships
     */

    // Define relationship to Keyword model
    public function keyword()
    {
        return $this->belongsTo(Keyword::class, 'keyword_uuid', 'keyword_uuid');
    }

    /**
     * Accessor for the month field to return a more human-readable format.
     */
    public function getMonthNameAttribute()
    {
        return date('F', mktime(0, 0, 0, $this->month, 10));
    }
}
