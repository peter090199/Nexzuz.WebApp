<?php

namespace App\Http\Controllers\CV;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CV\DAL\UserLanguages;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;



class UserLanguage extends Controller
{
    public function saveLanguage(Request $request)
    {
        $data = $request->all();

        if (!is_array($data)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid input format. Expected array of languages.',
            ], 422);
        }

        $currentUserCode = Auth::user()->code;

        $inserted = [];
        foreach ($data as $item) {
            $validator = Validator::make($item, [
                'language' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                continue; // Skip invalid items
            }

            $exists = UserLanguages::where('code', $currentUserCode)
                ->whereRaw('LOWER(language) = ?', [strtolower($item['language'])])
                ->exists();

            if (!$exists) {
                $maxTrans = UserLanguages::where('code', $currentUserCode)->max('transNo');
                $newTrans = $maxTrans ? $maxTrans + 1 : 1;

                $inserted[] = UserLanguages::create([
                    'code' => $currentUserCode,
                    'transNo' => $newTrans,
                    'language' => $item['language'],
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'inserted' => $inserted,
            'message' => count($inserted) . ' languages saved successfully.',
        ]);
    }

    //GET LANGUAGE BY CODE
    public function getLanguagesByCode()
    {
        $currentUserCode = Auth::user()->code;
        $languages = UserLanguages::where('code', $currentUserCode)->get();

        if ($languages->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No languages found for the given code.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'languages' => $languages,
        ]);
    }


}
