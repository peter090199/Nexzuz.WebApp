<?php

namespace App\Http\Controllers\CV;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CV\DAL\UserSkillsDAL;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserSkills extends Controller
{
    /**
     * Save a list of user skills.
     */
    public function saveSkills(Request $request)
    {
        $data = $request->all();

        if (!is_array($data)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid input format. Expected an array of skills.',
            ], 422);
        }

        $currentUserCode = Auth::user()->code;

        if (!$currentUserCode) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.',
            ], 401);
        }

        $inserted = [];

        foreach ($data as $item) {
            $validator = Validator::make($item, [
                'skills' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                continue; // Skip invalid items
            }

            $skillName = strtolower(trim($item['skills']));

            $exists = UserSkillsDAL::where('code', $currentUserCode)
                ->whereRaw('LOWER(skills) = ?', [$skillName])
                ->exists();

            if (!$exists) {
                $maxTrans = UserSkillsDAL::where('code', $currentUserCode)->max('transNo');
                $newTrans = $maxTrans ? $maxTrans + 1 : 1;

                $inserted[] = UserSkillsDAL::create([
                    'code' => $currentUserCode,
                    'transNo' => $newTrans,
                    'skills' => $item['skills'],
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Skill(s) saved successfully.',
        ]);
    }

    /**
     * Retrieve user skills.
     */
    public function getSkills()
    {
        try {
            $currentUserCode = Auth::id() ? Auth::user()->code : null;

            if (!$currentUserCode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access.',
                ], 401);
            }

            $data = UserSkillsDAL::where('code', $currentUserCode)
                ->orderBy('transNo', 'asc')
                ->get();

            if ($data->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No skills records found.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving skills.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a skill by ID.
     */
    public function delete($id)
    {
        try {
            $skill = UserSkillsDAL::find($id);

            if (!$skill) {
                return response()->json([
                    'success' => false,
                    'message' => 'Skill record not found.',
                ], 404);
            }

            $skill->delete();

            return response()->json([
                'success' => true,
                'message' => 'Skill deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete skill.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
