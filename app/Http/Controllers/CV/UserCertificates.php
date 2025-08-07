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

}
