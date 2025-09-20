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
//    public function saveEducation(Request $request)
//     {
//         if (!Auth::check()) {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'Unauthorized access.',
//             ], 401);
//         }

//         $currentUserCode = Auth::user()->code;

//         $validator = Validator::make($request->all(), [
//             'educations' => 'required|array|min:1',
//             'educations.*.id'                => 'nullable|integer|exists:usereducations,id',
//             'educations.*.highest_education' => 'required|string|max:255',
//             'educations.*.school_name'       => 'required|string|max:255',
//             'educations.*.start_month'       => 'nullable|string|max:20',
//             'educations.*.start_year'        => 'nullable|integer',
//             'educations.*.end_month'         => 'nullable|string|max:20',
//             'educations.*.end_year'          => 'nullable|integer',
//             'educations.*.status'            => 'required|string|max:100',
//         ]);

//         if ($validator->fails()) {
//             return response()->json([
//                 'success' => false,
//                 'errors' => $validator->errors(),
//             ], 422);
//         }

//         try {
//             DB::beginTransaction();

//             $maxTransNo = UserEducation::where('code', $currentUserCode)->max('transNo') ?? 0;
//             $savedRecords = [];

//             foreach ($request->educations as $item) {
//                 // If ID is provided, update existing
//                 if (!empty($item['id'])) {
//                     $education = UserEducation::where('id', $item['id'])
//                         ->where('code', $currentUserCode)
//                         ->first();

//                     if ($education) {
//                         $education->highest_education = $item['highest_education'];
//                         $education->school_name = $item['school_name'];
//                         $education->start_month = $item['start_month'] ?? null;
//                         $education->start_year = $item['start_year'] ?? null;
//                         $education->end_month = $item['end_month'] ?? null;
//                         $education->end_year = $item['end_year'] ?? null;
//                         $education->status = $item['status'];
//                         $education->updated_at = now();
//                         $education->save();

//                         $savedRecords[] = $education;
//                     }
//                 } else {
//                     // New entry → insert
//                     $maxTransNo++;

//                     $newEducation = new UserEducation([
//                         'code' => $currentUserCode,
//                         'transNo' => $maxTransNo,
//                         'highest_education' => $item['highest_education'],
//                         'school_name' => $item['school_name'],
//                         'start_month' => $item['start_month'] ?? null,
//                         'start_year' => $item['start_year'] ?? null,
//                         'end_month' => $item['end_month'] ?? null,
//                         'end_year' => $item['end_year'] ?? null,
//                         'status' => $item['status'],
//                         'created_at' => now(),
//                         'updated_at' => now(),
//                     ]);
//                     $newEducation->save();

//                     $savedRecords[] = $newEducation;
//                 }
//             }

//             DB::commit();

//             return response()->json([
//                 'success' => true,
//                 'message' => 'Education records processed successfully.',
//             ]);
//         } catch (\Exception $e) {
//             DB::rollBack();

//             return response()->json([
//                 'success' => false,
//                 'message' => 'Failed to process educations.',
//                 'error' => $e->getMessage(),
//             ], 500);
//         }
//     }

    public function saveEducation(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.',
            ], 401);
        }

        $currentUserCode = Auth::user()->code;

        // Step 1: Validate basic fields
        $validator = Validator::make($request->all(), [
            'educations' => 'required|array|min:1',
            'educations.*.id'                => 'nullable|integer|exists:usereducations,id',
            'educations.*.highest_education' => 'required|string|max:255',
            'educations.*.school_name'       => 'required|string|max:255',
            'educations.*.start_month'       => 'nullable|string|max:20',
            'educations.*.start_year'        => 'nullable|integer|min:1900|max:' . date('Y'),
            'educations.*.end_month'         => 'nullable|string|max:20',
            'educations.*.end_year'          => 'nullable|integer|min:1900|max:' . (date('Y') + 10),
            'educations.*.status'            => 'required|string|max:100',
        ]);

        // Step 2: Custom rule for start <= end (with readable messages)
        $validator->after(function ($validator) use ($request) {
            $months = [
                '01' => 'January', '02' => 'February', '03' => 'March',
                '04' => 'April',   '05' => 'May',      '06' => 'June',
                '07' => 'July',    '08' => 'August',   '09' => 'September',
                '10' => 'October', '11' => 'November', '12' => 'December',
            ];

            foreach ($request->educations as $index => $edu) {
                if (!empty($edu['start_year']) && !empty($edu['end_year'])) {
                    $startMonth = $edu['start_month'] ?? '01';
                    $endMonth   = $edu['end_month'] ?? '01';

                    $start = strtotime(($edu['start_year'] ?? 0) . '-' . $startMonth . '-01');
                    $end   = strtotime(($edu['end_year'] ?? 0) . '-' . $endMonth . '-01');

                    if ($end < $start) {
                        // Convert month numbers to names if possible
                        $startText = ($months[$startMonth] ?? $startMonth) . ' ' . ($edu['start_year'] ?? '');
                        $endText   = ($months[$endMonth]   ?? $endMonth)   . ' ' . ($edu['end_year'] ?? '');

                        $school = $edu['school_name'] ?? 'N/A';
                        $status = $edu['status'] ?? 'N/A';

                        $message = "Validation Error:\n"
                            . "School Name: {$school}\n"
                            . "Start: {$startText}\n"
                            . "End: {$endText}\n"
                            . "Status: {$status}\n"
                            . "❌ End date must not be earlier than start date.";

                        $validator->errors()->add("educations.$index.end_year", $message);
                    }
                }
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Step 3: Save or update records
        try {
            DB::beginTransaction();

            $maxTransNo = UserEducation::where('code', $currentUserCode)->max('transNo') ?? 0;
            $savedRecords = [];

            foreach ($request->educations as $item) {
                if (!empty($item['id'])) {
                    // Update existing record
                    $education = UserEducation::where('id', $item['id'])
                        ->where('code', $currentUserCode)
                        ->first();

                    if ($education) {
                        $education->highest_education = $item['highest_education'];
                        $education->school_name       = $item['school_name'];
                        $education->start_month       = $item['start_month'] ?? null;
                        $education->start_year        = $item['start_year'] ?? null;
                        $education->end_month         = $item['end_month'] ?? null;
                        $education->end_year          = $item['end_year'] ?? null;
                        $education->status            = $item['status'];
                        $education->updated_at        = now();
                        $education->save();

                        $savedRecords[] = $education;
                    }
                } else {
                    // Insert new record
                    $maxTransNo++;

                    $newEducation = new UserEducation([
                        'code'              => $currentUserCode,
                        'transNo'           => $maxTransNo,
                        'highest_education' => $item['highest_education'],
                        'school_name'       => $item['school_name'],
                        'start_month'       => $item['start_month'] ?? null,
                        'start_year'        => $item['start_year'] ?? null,
                        'end_month'         => $item['end_month'] ?? null,
                        'end_year'          => $item['end_year'] ?? null,
                        'status'            => $item['status'],
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ]);
                    $newEducation->save();

                    $savedRecords[] = $newEducation;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Education records processed successfully.',
                'data'    => $savedRecords
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to process educations.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function updateEducationById(Request $request, $id)
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
            $education = UserEducation::where('id', $id)
                ->where('code', $currentUserCode)
                ->first();
 
            if (!$education) {
                return response()->json([
                    'success' => false,
                    'message' => 'Education record not found.',
                ], 404);
            }

            $education->highest_education = $request->highest_education;
            $education->school_name = $request->school_name;
            $education->start_month = $request->start_month;
            $education->start_year = $request->start_year;
            $education->end_month = $request->end_month;
            $education->end_year = $request->end_year;
            $education->status = $request->status;
            $education->updated_at = now();
            $education->save();

            return response()->json([
                'success' => true,
                'message' => 'Education updated successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update education.',
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