<?php

namespace App\Http\Controllers\CV;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CV\DAL\UserLanguages;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class UserLanguage extends Controller
{
    public function saveLanguage(Request $request)
    {
        $validated = $request->validate([
            'language' => 'required|string|max:255',
        ]);

        $currentUserCode = Auth::user()->code;

        // Check if the language already exists for this user
        $exists = UserLanguages::where('code', $currentUserCode)
            ->whereRaw('LOWER(language) = ?', [strtolower($validated['language'])])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Language already exists for this user.'
            ], 409); // 409 Conflict
        }

        // Get latest transNo
        $maxTrans = UserLanguages::where('code', $currentUserCode)->max('transNo');
        $newTrans = $maxTrans ? $maxTrans + 1 : 1;

        // Save
        $record = UserLanguages::create([
            'code' => $currentUserCode,
            'transNo' => $newTrans,
            'language' => $validated['language'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Language saved successfully.',
            'data' => $record
        ]);
    }

}
