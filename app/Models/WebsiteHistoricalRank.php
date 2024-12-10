<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\WebsiteAnalysis;

class WebsiteHistoricalRank extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'project_code',
        'website_analysis_id',
        'year',
        'month',
        'organic_pos_1',
        'organic_pos_2_3',
        'organic_pos_4_10',
        'organic_pos_11_20',
        'organic_pos_21_30',
        'organic_pos_31_40',
        'organic_pos_41_50',
        'organic_pos_51_60',
        'organic_pos_61_70',
        'organic_pos_71_80',
        'organic_pos_81_90',
        'organic_pos_91_100',
        'organic_etv',
        'organic_impressions_etv',
        'organic_count',
        'organic_estimated_paid_traffic_cost',
        'organic_new',
        'organic_up',
        'organic_down',
        'organic_lost',
        'paid_pos_1',
        'paid_pos_2_3',
        'paid_pos_4_10',
        'paid_etv',
        'paid_impressions_etv',
        'paid_count',
        'paid_estimated_traffic_cost',
        'paid_new',
        'paid_up',
        'paid_down',
        'paid_lost'
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'organic_pos_1' => 'float',
        'organic_pos_2_3' => 'float',
        'organic_pos_4_10' => 'float',
        'organic_pos_11_20' => 'float',
        'organic_pos_21_30' => 'float',
        'organic_pos_31_40' => 'float',
        'organic_pos_41_50' => 'float',
        'organic_pos_51_60' => 'float',
        'organic_pos_61_70' => 'float',
        'organic_pos_71_80' => 'float',
        'organic_pos_81_90' => 'float',
        'organic_pos_91_100' => 'float',
        'organic_etv' => 'float',
        'organic_impressions_etv' => 'float',
        'organic_count' => 'integer',
        'organic_estimated_paid_traffic_cost' => 'float',
        'organic_new' => 'integer',
        'organic_up' => 'integer',
        'organic_down' => 'integer',
        'organic_lost' => 'integer',
        'paid_pos_1' => 'float',
        'paid_pos_2_3' => 'float',
        'paid_pos_4_10' => 'float',
        'paid_etv' => 'float',
        'paid_impressions_etv' => 'float',
        'paid_count' => 'integer',
        'paid_estimated_traffic_cost' => 'float',
        'paid_new' => 'integer',
        'paid_up' => 'integer',
        'paid_down' => 'integer',
        'paid_lost' => 'integer',
    ];

    public function analysis()
    {
        return $this->belongsTo(WebsiteAnalysis::class, 'website_analysis_id');
    }
}
