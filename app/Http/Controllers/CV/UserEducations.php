<?php

namespace App\Http\Controllers\CV;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Usereducation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class UserEducations extends Controller
{
    public function saveEducation(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.',
            ], 401);
        }

        $currentUserCode = Auth::user()->code;

        $validator = Validator::make($request->all(), [
            'educations' => 'required|array|min:1',
            'educations.*.highest_education' => 'required|string|max:255',
            'educations.*.school_name'       => 'required|string|max:255',
            'educations.*.start_month'       => 'nullable|string|max:20',
            'educations.*.start_year'        => 'nullable|integer',
            'educations.*.end_month'         => 'nullable|string|max:20',
            'educations.*.end_year'          => 'nullable|integer',
            'educations.*.status'            => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $educations = [];
            $maxTransNo = UserEducation::where('code', $currentUserCode)->max('transNo') ?? 0;

            foreach ($request->educations as $item) {
                $maxTransNo++;

                $educations[] = [
                    'code' => $currentUserCode,
                    'transNo' => $maxTransNo,
                    'highest_education' => $item['highest_education'],
                    'school_name' => $item['school_name'],
                    'start_month' => $item['start_month'] ?? null,
                    'start_year' => $item['start_year'] ?? null,
                    'end_month' => $item['end_month'] ?? null,
                    'end_year' => $item['end_year'] ?? null,
                    'status' => $item['status'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            UserEducation::insert($educations); // Bulk insert

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Educations saved successfully.',
                'data' => $educations,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to save educations.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getEducationsByCode()
    {
        try {
            $currentUserCode = Auth::user()->code;
            $educations = UserEducation::where('code', $currentUserCode)
                ->orderBy('transNo', 'asc')
                ->get();

            if ($educations->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No education records found for this code.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $educations,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving education records.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getEducationById($id)
    {
        try {
            $education = UserEducation::find($id);

            if (!$education) {
                return response()->json([
                    'success' => false,
                    'message' => 'Education record not found.',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'data' => $education,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving education record.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteEducation($id)
    {
        try {
            $education = UserEducation::find($id);

            if (!$education) {
                return response()->json([
                    'success' => false,
                    'message' => 'Education record not found.',
                ], 404);
            }

            $education->delete();

            return response()->json([
                'success' => true,
                'message' => 'Education record deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete education record.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}