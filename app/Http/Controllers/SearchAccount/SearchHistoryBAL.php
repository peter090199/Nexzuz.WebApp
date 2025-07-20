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

        // Optional: Extra check in case data is missing after validation
        if (empty($validated)) {
            return response()->json([
                'message' => '⚠️ No data provided.'
            ], 400);
        }

        // ✅ Save using DAL
        $result = $this->searchHistoryDAL->saveSearchHistory($validated);

        if (!$result) {
            return response()->json([
                'message' => '❌ Failed to save search history.'
            ], 500); // or 400 if it's a client-side error
        }

        return response()->json([
            'message' => '✅ Search history saved successfully.',
            'data'    => $validated
        ], 201);
    }

}
