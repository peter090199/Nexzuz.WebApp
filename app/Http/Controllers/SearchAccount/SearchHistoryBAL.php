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

        // 🚫 Prevent saving history of your own profile
        if (
            isset($validated['viewed_code']) &&
            $validated['viewer_code'] === $validated['viewed_code']
        ) {
            return response()->json([
                'message' => '⚠️ Cannot save history for your own profile.',
            ], 403);
        }

        // ❌ Prevent duplicate viewed_code for the same viewer_code
        $exists = $this->searchHistoryDAL->existsHistory($validated['viewer_code'], $validated['viewed_code']);
        if ($exists) {
            return response()->json([
                'message' => '⚠️ Duplicate viewed_code. This activity already exists.'
            ], 409);
        }

        // ✅ Save using DAL
        $result = $this->searchHistoryDAL->saveSearchHistory($validated);

        if (!$result) {
            return response()->json([
                'message' => '❌ Failed to save search history.'
            ], 500);
        }

        return response()->json([
            'message' => '✅ Search history saved successfully.',
            'data'    => $validated
        ], 201);
    }


}
