<?php

namespace App\Http\Controllers\CV;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Useremploymentrecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class UserWorkExperiences extends Controller
{
    public function saveEmployment(Request $request)
    {
        $data = $request->all();

        // ✅ If a single object is sent, wrap it into an array
        if (isset($data['company_name'])) {
            $data = [$data];
        }

        if (!is_array($data)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid input format. Expected an employment object or an array of employments.',
            ], 422);
        }

        $currentUserCode = Auth::user()->code ?? null;

        if (!$currentUserCode) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.',
            ], 401);
        }

        $inserted = [];

        foreach ($data as $item) {
            $validator = Validator::make($item, [
                'company_name'   => 'required|string|max:255',
                'position'       => 'required|string|max:255',
                'job_description'=> 'nullable|string',
                'date_completed' => 'nullable|date',
            ]);

            if ($validator->fails()) {
                Log::warning('Employment validation failed:', $validator->errors()->toArray());
                continue; // Skip invalid entries
            }

            $company  = strtolower(trim($item['company_name']));
            $position = strtolower(trim($item['position']));

            // Prevent duplicate for same company, position & date
            $exists = Useremploymentrecord::where('code', $currentUserCode)
                ->whereRaw('LOWER(company_name) = ?', [$company])
                ->whereRaw('LOWER(position) = ?', [$position])
                ->where('date_completed', $item['date_completed'])
                ->exists();

            if (!$exists) {
                $maxTrans = Useremploymentrecord::where('code', $currentUserCode)->max('transNo');
                $newTrans = $maxTrans ? $maxTrans + 1 : 1;

                try {
                    $inserted[] = Useremploymentrecord::create([
                        'code'            => $currentUserCode,
                        'transNo'         => $newTrans,
                        'company_name'    => $item['company_name'],
                        'position'        => $item['position'],
                        'job_description' => $item['job_description'] ?? null,
                        'date_completed'  => $item['date_completed'] ?? null,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to insert employment: ' . $e->getMessage());
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => count($inserted) . ' employment record(s) saved successfully.',
        ]);
    }

     public function getEmployment()
    {
        try {
            $currentUserCode = Auth::user()->code;

            if (!$currentUserCode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access.',
                ], 401);
            }

            $data = Useremploymentrecord::where('code', $currentUserCode)
                ->orderBy('transNo', 'asc')
                ->get();

            if ($data->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No employment records found.',
                    'data'    => [],
                ], 200);
            }

            return response()->json([
                'success' => true,
                'data'    => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving employment records.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

     public function deleteEmployment($id)
    {
        try {
            $employment = Useremploymentrecord::find($id);

            if (!$employment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employment record not found.',
                ], 404);
            }

            $employment->delete();

            return response()->json([
                'success' => true,
                'message' => 'Employment record deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete employment record.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function updateWorkExperience(Request $request, $id)
    {
        // ✅ Ensure the user is authenticated
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.',
            ], 401);
        }
        $currentUserCode = Auth::user()->code;

        // ✅ Validate incoming request
        $validator = Validator::make($request->all(), [
            'company_name'   => 'required|string|max:255',
            'position'       => 'required|string|max:255',
            'job_description'=> 'nullable|string',
            'date_completed'    => 'required|date_format:Y-m-d', // Enforce ISO format
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }
         // Future date check
            $futureCheck = ValidationController::futureDateCheck([
                ['date_completed' => $request->input('date_completed')]
            ], 'date_completed');

            if ($futureCheck !== true) {
                return $futureCheck;
            }

        try {
            // ✅ Find training record owned by current user
         $data = Useremploymentrecord::where('id', $id)
            ->where('code', $currentUserCode)
            ->first();


            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Work Experiences not found or unauthorized.',
                ], 404);
            }
            $data->update([
                'company_name'    => $request->input('company_name'),
                'position' => $request->input('position'),
                'job_description' => $request->input('job_description'),
                'date_completed'    => $request->input('date_completed'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Work Experience updated successfully.',
                'data'    => $data->fresh()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update Work Experiences.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


}
