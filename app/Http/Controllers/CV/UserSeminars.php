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

        // Validate basic fields first
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

        // Custom check for future dates
        foreach ($request->seminars as $item) {
            if (strtotime($item['date_completed']) > strtotime(date('Y-m-d'))) {
                return response()->json([
                    'success' => false,
                    'message' => 'The completion date cannot be in the future.',
                ], 422);
            }
        }

        try {
            DB::beginTransaction();

            $maxTransNo = Userseminar::where('code', $currentUserCode)->max('transNo') ?? 0;
            $savedRecords = [];
            $insertedCount = 0;
            $updatedCount = 0;

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
                        $updatedCount++;
                    }
                } else {
                    // Insert new seminar
                    $maxTransNo++;

                    $newSeminar = Userseminar::create([
                        'code' => $currentUserCode,
                        'transNo' => $maxTransNo,
                        'seminar_title' => $item['seminar_title'],
                        'seminar_provider' => $item['seminar_provider'],
                        'date_completed' => $item['date_completed'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $savedRecords[] = $newSeminar;
                    $insertedCount++;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Seminar saved successfully. Inserted: $insertedCount, Updated: $updatedCount.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to process seminar records.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function saveSeminarxx(Request $request)
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
            $insertedCount = 0;
            $updatedCount = 0;

            foreach ($request->seminars as $item) {
                if (!empty($item['id'])) {
                    // 🔁 Update existing seminar
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
                        $updatedCount++;
                    }
                } else {
                    // ➕ Insert new seminar
                    $maxTransNo++;

                    $newSeminar = Userseminar::create([
                        'code' => $currentUserCode,
                        'transNo' => $maxTransNo,
                        'seminar_title' => $item['seminar_title'],
                        'seminar_provider' => $item['seminar_provider'],
                        'date_completed' => $item['date_completed'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $savedRecords[] = $newSeminar;
                    $insertedCount++;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Seminar saved successfully. Inserted: $insertedCount, Updated: $updatedCount.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to process seminar records.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateSeminar(Request $request, $id)
    {
        // ✅ Check authentication
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.',
            ], 401);
        }

        $currentUserCode = Auth::user()->code;

        // ✅ Validate request
        $validator = Validator::make($request->all(), [
            'seminar_title'    => 'required|string|max:255',
            'seminar_provider' => 'required|string|max:255',
            'date_completed'   => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            // ✅ Find seminar that belongs to this user
            $seminar = Userseminar::where('id', $id)
                ->where('code', $currentUserCode)
                ->first();

            if (!$seminar) {
                return response()->json([
                    'success' => false,
                    'message' => 'Seminar not found or unauthorized.',
                ], 404);
            }

            // ✅ Update seminar
            $seminar->update([
                'seminar_title'    => $request->seminar_title,
                'seminar_provider' => $request->seminar_provider,
                'date_completed'   => $request->date_completed,
            ]);

            // ✅ Return fresh updated record
            return response()->json([
                'success' => true,
                'message' => 'Seminar updated successfully.',
                'data'    => $seminar->fresh()
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update seminar.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
    public function getSeminarByCode()
    {
        try {
            // Check if user is authenticated
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access.',
                ], 401);
            }

            $currentUserCode = Auth::user()->code;

            $data = Userseminar::where('code', $currentUserCode)
                ->orderBy('transNo', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => $data->isEmpty() ? 'No seminar records found.' : 'Seminar records retrieved successfully.',
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
