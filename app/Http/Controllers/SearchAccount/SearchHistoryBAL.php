<?php

namespace App\Http\Controllers\SearchAccount;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DAL\SearchHistoryDAL;
use Illuminate\Support\Facades\Auth;

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
            'activity_type' => 'required|string',
            'viewed_code'   => 'nullable',
        ]);

        // Automatically set viewer_code to authenticated user
        $viewer_code = Auth::user()->code;
        $validated['viewer_code'] = $viewer_code;

        if (empty($validated)) {
            return response()->json([
                'message' => '⚠️ No data provided.'
            ], 500);
        }

        // 🚫 Prevent saving history of your own profile
        if (
            isset($validated['viewed_code']) &&
            $viewer_code === $validated['viewed_code']
        ) {
            return response()->json([
                'message' => '⚠️ Cannot save history for your own profile.',
            ], 403);
        }

        // 🚫 Reject if viewed_code is non-empty and not numeric
        if (
            isset($validated['viewed_code']) &&
            (!is_numeric($validated['viewed_code']) || $validated['viewed_code'] === '')
        ) {
            return response()->json([
                'message' => '❌ Invalid viewed_code. It must be a number or null.'
            ], 400);
        }

        // ❌ Prevent duplicate viewed_code for same viewer_code
        $exists = $this->searchHistoryDAL->existsHistory($viewer_code, $validated['viewed_code']);
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

    // public function saveSearchHistory(Request $request)
    // {
    //     $validated = $request->validate([
    //         'viewer_code'   => 'required|integer',
    //         'activity_type' => 'required|string',
    //         'viewed_code'   => 'nullable', // validate manually after
    //     ]);


    //     if (empty($validated)) {
    //         return response()->json([
    //             'message' => '⚠️ No data provided.'
    //         ], 500);
    //     }

    //     // 🚫 Prevent saving history of your own profile
    //     if (
    //         isset($validated['viewed_code']) &&
    //         $validated['viewer_code'] === $validated['viewed_code']
    //     ) {
    //         return response()->json([
    //             'message' => '⚠️ Cannot save history for your own profile.',
    //         ], 403);
    //     }
    //     // 🚫 Reject if viewed_code is non-empty and not numeric
    //     if (
    //         isset($validated['viewed_code']) &&
    //         (!is_numeric($validated['viewed_code']) || $validated['viewed_code'] === '')
    //     ) {
    //         return response()->json([
    //             'message' => '❌ Invalid viewed_code. It must be a number or null.'
    //         ], 400);
    //     }

    //     if (
    //      !is_numeric($validated['viewer_code']) ||
    //         $validated['viewer_code'] === ''
    //     ) {
    //         return response()->json([
    //             'message' => '❌ Invalid viewer_code. It must be a number.'
    //         ], 400);
    //     }


    //     // ❌ Prevent duplicate viewed_code for the same viewer_code
    //     $exists = $this->searchHistoryDAL->existsHistory($validated['viewer_code'], $validated['viewed_code']);
    //     if ($exists) {
    //         return response()->json([
    //             'message' => '⚠️ Duplicate viewed_code. This activity already exists.'
    //         ], 409);
    //     }

    //     // ✅ Save using DAL
    //     $result = $this->searchHistoryDAL->saveSearchHistory($validated);

    //     if (!$result) {
    //         return response()->json([
    //             'message' => '❌ Failed to save search history.'
    //         ], 500);
    //     }

    //     return response()->json([
    //         'message' => '✅ Search history saved successfully.',
    //         'data'    => $validated
    //     ], 201);
    // }

    public function getSearchHistory()
    {
        return $this->searchHistoryDAL->getSearchHistory();
    }
        public function deleteSearchHistory()
    {
        return $this->searchHistoryDAL->deleteSearchHistory();
    }




}
