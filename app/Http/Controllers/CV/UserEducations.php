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
            'highest_education' => 'required|string|max:255',
            'school_name'       => 'required|string|max:255',
            'start_month'       => 'nullable|string|max:20',
            'start_year'        => 'nullable|integer',
            'end_month'         => 'nullable|string|max:20',
            'end_year'          => 'nullable|integer',
            'status'            => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            // ✅ Correct use of variable
            $maxTransNo = UserEducation::where('code', $currentUserCode)->max('transNo');
            $newTransNo = $maxTransNo ? $maxTransNo + 1 : 1;

            $education = new UserEducation();
            $education->code = $currentUserCode;
            $education->transNo = $newTransNo;
            $education->highest_education = $request->highest_education;
            $education->school_name = $request->school_name;
            $education->start_month = $request->start_month;
            $education->start_year = $request->start_year;
            $education->end_month = $request->end_month;
            $education->end_year = $request->end_year;
            $education->status = $request->status;
            $education->save();

            DB::commit(); // ✅ Don't forget to commit the transaction

            return response()->json([
                'success' => true,
                'message' => 'Education saved successfully.',
                'data'    => $education,
            ]);
        } catch (\Exception $e) {
            DB::rollBack(); // ✅ Rollback on error
            return response()->json([
                'success' => false,
                'message' => 'Failed to save education.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

}