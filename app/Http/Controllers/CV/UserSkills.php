<?php

namespace App\Http\Controllers\CV;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Userskill;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class UserSkills extends Controller
{
    public function save(Request $request)
    {
        $data = $request->all();

        if (!is_array($data)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid input format. Expected array of skills.',
            ], 422);
        }

        $currentUserCode = Auth::user()->code;

        $inserted = [];
        foreach ($data as $item) {
            $validator = Validator::make($item, [
                'skills' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                continue; // Skip invalid items
            }

            $exists = Userskill::where('code', $currentUserCode)
                ->whereRaw('LOWER(skills) = ?', [strtolower($item['skills'])])
                ->exists();

            if (!$exists) {
                $maxTrans = Userskill::where('code', $currentUserCode)->max('transNo');
                $newTrans = $maxTrans ? $maxTrans + 1 : 1;

                $inserted[] = Userskill::create([
                    'code' => $currentUserCode,
                    'transNo' => $newTrans,
                    'skills' => $item['skills'],
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => count($inserted) . ' Skills saved successfully.',
        ]);
    }

    public function getSkills()
    {
        try {
            $currentUserCode = Auth::user()->code;
            $data = Userskill::where('code', $currentUserCode)
                ->orderBy('transNo', 'asc')
                ->get();

            if ($data->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No skills records found for this code.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving skills records.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function delete($id)
    {
        try {
            $data = Userskill::find($id);

            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Skills record not found.',
                ], 404);
            }

            $data->delete();

            return response()->json([
                'success' => true,
                'message' => 'Skills  deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete Skills record.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
