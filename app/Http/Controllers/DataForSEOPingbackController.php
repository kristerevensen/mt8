<?php

namespace App\Http\Controllers;

use App\Models\Keyword;
use App\Models\KeywordSearchVolume;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DataForSEOPingbackController extends Controller
{
    /**
     * Handle pingback or postback from DataForSEO.
     */
    public function handlePingback(Request $request)
    {
        // Extract task id and tag (you may also want to validate them)
        $taskId = $request->query('id');
        $projectCode = $request->query('tag');

        // Extract the result from the DataForSEO postback
        $taskResult = $request->input('tasks.0.result');

        // Log any unexpected results
        if (empty($taskResult)) {
            Log::warning('No result found in DataForSEO pingback.');
            return response()->json(['message' => 'No result found'], 400);
        }

        // Find the relevant keyword using the project code
        $keyword = Keyword::where('keyword', $taskResult[0]['keyword'])->first();

        if ($keyword) {
            // Update keyword analysis data
            $keyword->update([
                'spell' => $taskResult[0]['spell'],
                'location_code' => $taskResult[0]['location_code'],
                'language_code' => $taskResult[0]['language_code'],
                'search_partners' => $taskResult[0]['search_partners'],
                'competition' => $taskResult[0]['competition'],
                'competition_index' => $taskResult[0]['competition_index'],
                'search_volume' => $taskResult[0]['search_volume'],
                'low_top_of_page_bid' => $taskResult[0]['low_top_of_page_bid'],
                'high_top_of_page_bid' => $taskResult[0]['high_top_of_page_bid'],
                'cpc' => $taskResult[0]['cpc'],
                'analyzed_at' => now(),
            ]);

            // Insert monthly search volumes
            foreach ($taskResult[0]['monthly_searches'] as $monthData) {
                KeywordSearchVolume::updateOrCreate(
                    [
                        'keyword_uuid' => $keyword->keyword_uuid,
                        'year' => $monthData['year'],
                        'month' => $monthData['month']
                    ],
                    [
                        'search_volume' => $monthData['search_volume']
                    ]
                );
            }

            return response()->json(['message' => 'Keyword data updated successfully']);
        } else {
            Log::error('Keyword not found for project code: ' . $projectCode);
            return response()->json(['error' => 'Keyword not found'], 404);
        }
    }
}
