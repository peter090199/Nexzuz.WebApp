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
    
    public function store(Request $request)
    {
        // ✅ Validate the request
        $validated = $request->validate([
            'viewer_code' => 'required|string',
            'activity_type' => 'required|string',
            'viewed_code' => 'nullable|string',
        ]);

        // ✅ Save using DAL
        $result = $this->searchHistoryDAL->saveSearchHistory($validated);

        return response()->json([
            'message' => 'Search history saved successfully.',
            'data' => $result
        ], 201);
    }
}
