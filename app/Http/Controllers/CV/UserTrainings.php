<?php

namespace App\Http\Controllers\CV;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Usertraining;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\CV\ValidationController;


class UserTrainings extends Controller
{
    // public function saveTrainings(Request $request)
    // {
    //     $data = $request->all();
    //     $futureCheck = ValidationController::futureDateCheck($request->seminars, 'date_completed');
        
    //     if ($futureCheck !== true) {
    //         return $futureCheck; // returns JSON response if invalid
    //     }

        
    //     // ✅ If a single object is sent, wrap it into an array
    //     if (isset($data['training_title'])) {
    //         $data = [$data];
    //     }

    //     if (!is_array($data)) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Invalid input format. Expected a training object or an array of trainings.',
    //         ], 422);
    //     }

    //     $currentUserCode = Auth::user()->code;
        
    //     if (!$currentUserCode) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Unauthorized access.',
    //         ], 401);
    //     }

    //     $inserted = [];

    //     foreach ($data as $item) {
    //         $validator = Validator::make($item, [
    //             'training_title' => 'required|string|max:255',
    //             'training_provider' => 'nullable|string|max:255',
    //             'date_completed' => 'required|date',
    //         ]);

    //         if ($validator->fails()) {
    //             Log::warning('Training validation failed:', $validator->errors()->toArray());
    //             continue;
    //         }

    //        foreach ($request->seminars as $item) {
    //         if (strtotime($item['date_completed']) > strtotime(date('Y-m-d'))) {
    //           return response()->json([
    //                 'success' => false,
    //                 'message' => 'The completion date cannot be in the future.',
    //             ], 422);

    //         }
    //        }
    //         $title = strtolower(trim($item['training_title']));
    //         $exists = Usertraining::where('code', $currentUserCode)
    //             ->whereRaw('LOWER(training_title) = ?', [$title])
    //             ->where('date_completed', $item['date_completed'])
    //             ->exists();

    //             if (!$exists) {
    //             $maxTrans = Usertraining::where('code', $currentUserCode)->max('transNo');
    //             $newTrans = $maxTrans ? $maxTrans + 1 : 1;

    //             try {
    //                 $inserted[] = Usertraining::create([
    //                     'code' => $currentUserCode,
    //                     'transNo' => $newTrans,
    //                     'training_title' => $item['training_title'],
    //                     'training_provider' => $item['training_provider'] ?? null,
    //                     'date_completed' => $item['date_completed'],
    //                 ]);
    //             } catch (\Exception $e) {
    //                 Log::error('Failed to insert training: ' . $e->getMessage());
    //             }
    //         }
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'message' => count($inserted) . ' training(s) saved successfully.',
    //     ]);
    // }

    public function saveTrainings(Request $request)
    {
        $data = $request->all();

        // Ensure $data is an array
        if (isset($data['training_title'])) {
            $data = [$data];
        }

        if (!is_array($data)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid input format. Expected a training object or an array of trainings.',
            ], 422);
        }

        // Future date check using reusable function
        $futureCheck = ValidationController::futureDateCheck($data, 'date_completed');
        if ($futureCheck !== true) {
            return $futureCheck; // returns JSON response if invalid
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
                'training_title' => 'required|string|max:255',
                'training_provider' => 'nullable|string|max:255',
                'date_completed' => 'required|date',
            ]);

            if ($validator->fails()) {
                Log::warning('Training validation failed:', $validator->errors()->toArray());
                continue; // skip invalid item
            }

            $title = strtolower(trim($item['training_title']));
            $exists = Usertraining::where('code', $currentUserCode)
                ->whereRaw('LOWER(training_title) = ?', [$title])
                ->where('date_completed', $item['date_completed'])
                ->exists();

            if (!$exists) {
                $maxTrans = Usertraining::where('code', $currentUserCode)->max('transNo');
                $newTrans = $maxTrans ? $maxTrans + 1 : 1;

                try {
                    $inserted[] = Usertraining::create([
                        'code' => $currentUserCode,
                        'transNo' => $newTrans,
                        'training_title' => $item['training_title'],
                        'training_provider' => $item['training_provider'] ?? null,
                        'date_completed' => $item['date_completed'],
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to insert training: ' . $e->getMessage());
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => count($inserted) . ' training(s) saved successfully.',
        ]);
    }

    public function updateTrainings(Request $request, $id)
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
            'training_title'    => 'required|string|max:255',
            'training_provider' => 'required|string|max:255',
            'date_completed'    => 'required|date_format:Y-m-d', // Enforce ISO format
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        // ✅ Future date check using reusable function
      $futureCheck = ValidationController::futureDateCheck([$request->all()], 'date_completed');
        if ($futureCheck !== true) {
            return $futureCheck; // returns JSON response if invalid
        }



        try {
            // ✅ Find training record owned by current user
         $training = Usertraining::where('id', $id)
            ->where('code', $currentUserCode)
            ->first();


            if (!$training) {
                return response()->json([
                    'success' => false,
                    'message' => 'Training not found or unauthorized.',
                ], 404);
            }

            // ✅ Update training details
            $training->update([
                'training_title'    => $request->input('training_title'),
                'training_provider' => $request->input('training_provider'),
                'date_completed'    => $request->input('date_completed'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Training updated successfully.',
                'data'    => $training->fresh()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update training.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function getTrainings()
    {
        try {
           $currentUserCode = Auth::user()->code;

            if (!$currentUserCode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access.',
                ], 401);
            }

            $data = Usertraining::where('code', $currentUserCode)
                ->orderBy('transNo', 'asc')
                ->get();

            if ($data->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No training records found.',
                    'data' => [],
                ], 200); // No error, just empty
            }

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving trainings.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteTraining($id)
    {
        try {
            $training = Usertraining::find($id);

            if (!$training) {
                return response()->json([
                    'success' => false,
                    'message' => 'Training record not found.',
                ], 404);
            }

            $training->delete();

            return response()->json([
                'success' => true,
                'message' => 'Training deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete training.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


}
