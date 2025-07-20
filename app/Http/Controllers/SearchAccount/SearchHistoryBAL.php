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

       // 🚫 Prevent saving if the viewer is trying to log their own profile
        if (
            isset($validated['viewed_code']) &&
            $validated['viewer_code'] === $validated['viewed_code']
        ) {
            return response()->json([
                'message' => '⚠️ You cannot save activity for your own code.',
            ], 403); // Forbidden
        }

      
        if (!$result) {
            return response()->json([
                'message' => 'Dulicate viewed_code to save search history.'
            ], 400); // or 400 if it's a client-side error
        }

          // ✅ Save using DAL
        $result = $this->searchHistoryDAL->saveSearchHistory($validated);

        return response()->json([
            'message' => '✅ Search history saved successfully.',
            'data'    => $validated
        ], 201);
    }

}
