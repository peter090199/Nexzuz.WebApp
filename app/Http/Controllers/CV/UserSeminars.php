<?php

namespace App\Http\Controllers\CV;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Userseminar;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserSeminars extends Controller
{
    public function saveSeminar(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.',
            ], 401);
        }

        $currentUserCode = Auth::user()->code;

        $validator = Validator::make($request->all(), [
            'seminars' => 'required|array|min:1',
            'seminars.*.id' => 'nullable|integer|exists:userseminars,id',
            'seminars.*.seminar_title' => 'required|string|max:255',
            'seminars.*.seminar_provider' => 'required|string|max:255',
            'seminars.*.date_completed' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $maxTransNo = Userseminar::where('code', $currentUserCode)->max('transNo') ?? 0;
            $savedRecords = [];

            foreach ($request->seminars as $item) {
                if (!empty($item['id'])) {
                    // Update existing seminar
                    $seminar = Userseminar::where('id', $item['id'])
                        ->where('code', $currentUserCode)
                        ->first();

                    if ($seminar) {
                        $seminar->seminar_title = $item['seminar_title'];
                        $seminar->seminar_provider = $item['seminar_provider'];
                        $seminar->date_completed = $item['date_completed'];
                        $seminar->updated_at = now();
                        $seminar->save();

                        $savedRecords[] = $seminar;
                    }
                } else {
                    // Insert new seminar
                    $maxTransNo++;

                    $newSeminar = new Userseminar([
                        'code' => $currentUserCode,
                        'transNo' => $maxTransNo,
                        'seminar_title' => $item['seminar_title'],
                        'seminar_provider' => $item['seminar_provider'],
                        'date_completed' => $item['date_completed'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $newSeminar->save();

                    $savedRecords[] = $newSeminar;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Seminar records successfully.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to process seminars.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getSeminarByCode()
    {
        try {
            $currentUserCode = Auth::user()->code;
            $data = Userseminar::where('code', $currentUserCode)
                ->orderBy('transNo', 'asc')
                ->get();

            if ($data->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No seminar records found for this code.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving seminar records.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function delete($id)
    {
        try {
            $education = Userseminar::find($id);

            if (!$education) {
                return response()->json([
                    'success' => false,
                    'message' => 'Seminar record not found.',
                ], 404);
            }

            $education->delete();

            return response()->json([
                'success' => true,
                'message' => 'Seminar  deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete seminar record.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
