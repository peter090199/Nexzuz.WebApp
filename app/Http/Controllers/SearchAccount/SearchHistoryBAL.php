<?php

namespace App\Http\Controllers\SearchAccount;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DAL\SearchHistoryDAL;

class SearchHistoryBAL extends Controller
{
    protected $searchHistoryDAL;

    public function __construct(SearchHistoryDAL $searchHistoryDAL)
    {
        $this->searchHistoryDAL = $searchHistoryDAL;
    }
    
    public function saveSearchHistory(Request $request)
    {
        $validated = $request->validate([
            'viewer_code'   => 'required|string',
            'activity_type' => 'required|string',
            'viewed_code'   => 'nullable|string',
        ]);

    
         if (empty($validated)) {
            return response()->json([
                'message' => '⚠️ No data provided.'
            ], 400);
        }

        if (!$result) {
            return response()->json([
                'message' => '❌ Failed to save search history.'
            ], 500); // or 400 depending on context
        }

        // ✅ Save using DAL
        $result = $this->searchHistoryDAL->saveSearchHistory($validated);
        return response()->json([
            'message' => '✅ Search history saved successfully.',
            'data'    => $validated
        ], 201);
    }

}
