<?php

namespace App\Http\Controllers\CV;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Usertraining;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class UserTrainings extends Controller
{
    public function saveTrainings(Request $request)
    {
        $data = $request->all();

        if (!is_array($data)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid input format. Expected an array of trainings.',
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
                'training_title' => 'required|string|max:255',
                'training_provider' => 'nullable|string|max:255',
                'date_completed' => 'required|date',
            ]);

            if ($validator->fails()) {
                Log::warning('Training validation failed:', $validator->errors()->toArray());
                continue;
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
