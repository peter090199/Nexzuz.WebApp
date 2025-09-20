<?php

namespace App\Http\Controllers\CV;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Usercertificate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class UserCertificates extends Controller
{
    public function saveCertificates(Request $request)
    {
        $data = $request->all();

        if (!is_array($data)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid input format. Expected an array of certificates.',
            ], 422);
        }
       // ✅ Future date validation using reusable function
        $futureCheck = ValidationController::futureDateCheck($data, 'date_completed');
        if ($futureCheck !== true) {
            return $futureCheck; // returns JSON response if invalid
        }
        
        $code = Auth::user()->code;

        if (!$code) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.',
            ], 401);
        }

        $inserted = [];

        foreach ($data as $item) {
            $validator = Validator::make($item, [
                'certificate_title' => 'required|string|max:255',
                'certificate_provider' => 'required|string|max:255',
                'date_completed' => 'required|date',
            ]);

            if ($validator->fails()) {
                Log::warning('Certificate validation failed', $validator->errors()->toArray());
                continue;
            }

            $maxTrans = Usercertificate::where('code', $code)->max('transNo');
            $newTrans = $maxTrans ? $maxTrans + 1 : 1;

            try {
                $inserted[] = Usercertificate::create([
                    'code' => $code,
                    'transNo' => $newTrans,
                    'certificate_title' => $item['certificate_title'],
                    'certificate_provider' => $item['certificate_provider'],
                    'date_completed' => $item['date_completed'],
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to save certificate: ' . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => count($inserted) . ' certificate(s) saved successfully.',
        ]);
    }

    public function getCertificates()
    {
        $code = Auth::check() ? Auth::user()->code : null;

        if (!$code) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.',
            ], 401);
        }

        $certificates = Usercertificate::where('code', $code)->orderBy('transNo')->get();

        if ($certificates->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No certificate records found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $certificates,
        ]);
    }

        public function deleteCertificate($id)
        {
            try {
                $certificate = Usercertificate::find($id);

                if (!$certificate) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Certificate not found.',
                    ], 404);
                }

                $certificate->delete();

                return response()->json([
                    'success' => true,
                    'message' => 'Certificate deleted successfully.',
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting certificate.',
                    'error' => $e->getMessage(),
                ], 500);
            }
        }

   public function updateCertificates(Request $request, $id)
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
            'certificate_title'    => 'required|string|max:255',
            'certificate_provider' => 'required|string|max:255',
            'date_completed'    => 'required|date_format:Y-m-d', // Enforce ISO format
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            // ✅ Find training record owned by current user
         $data = Usercertificate::where('id', $id)
            ->where('code', $currentUserCode)
            ->first();


            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cerificate not found or unauthorized.',
                ], 404);
            }
            $data->update([
                'certificate_title'    => $request->input('certificate_title'),
                'certificate_provider' => $request->input('certificate_provider'),
                'date_completed'    => $request->input('date_completed'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Certificate updated successfully.',
                'data'    => $data->fresh()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update Certificate.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

}
