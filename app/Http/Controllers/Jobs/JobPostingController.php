<?php

namespace App\Http\Controllers\Jobs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Jobs\JobPosting;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Jobs\QuestionController;
use App\Models\Jobs\Question;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class JobPostingController extends Controller
{

    // public function saveOrUpdateJobPosting(Request $request, $transNo = null)
    // {
    //     try {
    //         $user = Auth::user();

    //         // ✅ Validation
    //         $validated = $request->validate([
    //             'job_name'        => 'required|string|max:255',
    //             'job_position'    => 'required|string|max:255',
    //             'job_description' => 'required|string',
    //             'job_about'       => 'required|string',
    //             'qualification'   => 'required|string',
    //             'work_type'       => 'required|string',
    //             'job_image'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
    //             'location'        => 'required|string|max:255',
    //             'benefits'        => 'required|string|max:255',
    //             'question_text'   => 'required|array|min:1',
    //             'question_text.*' => 'required|string|max:255',

    //             // ✅ ADD THIS
    //             'answer_type'          => 'required|array',
    //             'answer_type.*'        => 'required|in:yes,no,general',
    //         ]);

    //         DB::beginTransaction();

    //         // if ($request->hasFile('job_image')) {
    //         //     $file = $request->file('job_image');
    //         //     $uuid = Str::uuid();

    //         //     $folderPath = "uploads/{$user->code}/JobPosting/{$uuid}";
    //         //     $fileName = time() . '.' . $file->getClientOriginalExtension();

    //         //     $filePath = $file->storeAs($folderPath, $fileName, 'public');

    //         //     // ✅ FIX: proper public URL
    //         //     $validated['job_image'] = Storage::disk('public')->url($filePath);
    //         // }
    //         if ($request->hasFile('job_image')) {
    //             $file = $request->file('job_image');
    //             $uuid = Str::uuid();
    //             $folderPath = "uploads/{$user->code}/JobPosting/{$uuid}";
    //             $fileName = time() . '.' . $file->getClientOriginalExtension();
    //             $filePath = $file->storeAs($folderPath, $fileName, 'public');
    //             $validated['job_image'] = "/storage/app/public/" . $filePath;
    //         }

    //         // ---------- CREATE NEW JOB ----------
    //         if (!$transNo) {
    //             $lastTrans = JobPosting::orderByDesc('job_id')->first();
    //             $lastNumber = $lastTrans ? intval(substr($lastTrans->transNo, -6)) : 0;
    //             $transNo = "TR-" . str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);

    //             $job = JobPosting::create([
    //                 'job_name'        => $validated['job_name'],
    //                 'job_position'    => $validated['job_position'],
    //                 'job_description' => $validated['job_description'],
    //                 'job_about'       => $validated['job_about'],
    //                 'qualification'   => $validated['qualification'],
    //                 'work_type'       => $validated['work_type'],
    //                 'job_image'       => $validated['job_image'] ?? null,
    //                 'location'        => $validated['location'],
    //                 'benefits'        => $validated['benefits'],
    //                 'code'            => $user->code,
    //                 'role_code'       => $user->role_code,
    //                 'fullname'        => $user->fullname,
    //                 'is_online'       => $user->is_online,
    //                 'company'         => $user->company,
    //                 'transNo'         => $transNo,
    //             ]);

    //             // Save all questions
    //             foreach ($validated['question_text'] as $index => $questionText) {
    //                 Question::create([
    //                     'question_text' => $questionText,
    //                     'answer_text'   => $validated['answer_text'][$index] ?? 'yes',
    //                     'job_name'      => $validated['job_name'],
    //                     'role_code'     => $user->role_code,
    //                     'code'          => $user->code,
    //                     'fullname'      => $user->fullname,
    //                     'company'       => $user->company,
    //                     'transNo'       => $transNo,
    //                 ]);
    //             }
    //         }
    //         // ---------- UPDATE EXISTING JOB ----------
    //         else {
    //             $job = JobPosting::where('transNo', $transNo)->firstOrFail();

    //             // Update job fields
    //             $job->update([
    //                 'job_name'        => $validated['job_name'],
    //                 'job_position'    => $validated['job_position'],
    //                 'job_description' => $validated['job_description'],
    //                 'job_about'       => $validated['job_about'],
    //                 'qualification'   => $validated['qualification'],
    //                 'work_type'       => $validated['work_type'],
    //                 'location'        => $validated['location'],
    //                 'benefits'        => $validated['benefits'],
    //                 'job_image'       => $validated['job_image'] ?? $job->job_image,
    //             ]);

    //             // Update questions:
    //             // Remove old questions for this job
    //             Question::where('transNo', $transNo)->delete();

    //             // Insert new/updated questions
    //             foreach ($validated['question_text'] as $index => $questionText) {
    //                 Question::create([
    //                     'question_text' => $questionText,
    //                     'answer_text'   => $validated['answer_text'][$index] ?? 'yes',
    //                     'job_name'      => $validated['job_name'],
    //                     'role_code'     => $user->role_code,
    //                     'code'          => $user->code,
    //                     'fullname'      => $user->fullname,
    //                     'company'       => $user->company,
    //                     'transNo'       => $transNo,
    //                 ]);
    //             }
    //         }

    //         DB::commit();

    //         return response()->json([
    //             'success' => true,
    //             'message' => $transNo ? 'Job updated successfully' : 'Job saved successfully',
    //             'transNo' => $transNo,
    //         ], $transNo ? 200 : 201);
    //     } catch (\Illuminate\Validation\ValidationException $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'message' => 'Validation failed.',
    //             'success' => false,
    //             'errors'  => $e->errors(),
    //         ], 422);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'message' => 'Something went wrong while saving the job.',
    //             'success' => false,
    //             'error'   => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    // public function saveOrUpdateJobPosting(Request $request, $transNo = null)
    // {
    //     try {
    //         $user = Auth::user();

    //         // ✅ Validation
    //         $validated = $request->validate([
    //             'job_name'        => 'required|string|max:255',
    //             'job_position'    => 'required|string|max:255',
    //             'job_description' => 'required|string',
    //             'job_about'       => 'required|string',
    //             'qualification'   => 'required|string',
    //             'work_type'       => 'required|string',
    //             'job_image'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
    //             'location'        => 'required|string|max:255',
    //             'benefits'        => 'required|string|max:255',

    //             // ✅ SALARY RANGE
    //             'min_salary'      => 'required|numeric|min:0',
    //             'max_salary'      => 'required|numeric|min:0|gte:min_salary',

    //             'question_text'   => 'required|array|min:1',
    //             'question_text.*' => 'required|string|max:255',

    //             // ✅ ADD THIS
    //             'answer_type'          => 'required|array',
    //             'answer_type.*'        => 'required|in:yes,no,general',
    //         ]);

    //         DB::beginTransaction();

    //         if ($request->hasFile('job_image')) {
    //             $file = $request->file('job_image');
    //             $uuid = Str::uuid();
    //             $folderPath = "uploads/{$user->code}/JobPosting/{$uuid}";
    //             $fileName = time() . '.' . $file->getClientOriginalExtension();
    //             $filePath = $file->storeAs($folderPath, $fileName, 'public');
    //             $validated['job_image'] = "/storage/app/public/" . $filePath;
    //         }

    //         // ---------- CREATE NEW JOB ----------
    //         if (!$transNo) {
    //             $lastTrans = JobPosting::orderByDesc('job_id')->first();
    //             $lastNumber = $lastTrans ? intval(substr($lastTrans->transNo, -6)) : 0;
    //             $transNo = "TR-" . str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);

    //             $job = JobPosting::create([
    //                 'job_name'        => $validated['job_name'],
    //                 'job_position'    => $validated['job_position'],
    //                 'job_description' => $validated['job_description'],
    //                 'job_about'       => $validated['job_about'],
    //                 'qualification'   => $validated['qualification'],
    //                 'work_type'       => $validated['work_type'],
    //                 'job_image'       => $validated['job_image'] ?? null,
    //                 'location'        => $validated['location'],
    //                 'benefits'        => $validated['benefits'],

    //                 // ✅ SALARY RANGE
    //                 'min_salary'      => $validated['min_salary'],
    //                 'max_salary'      => $validated['max_salary'],

    //                 'code'            => $user->code,
    //                 'role_code'       => $user->role_code,
    //                 'fullname'        => $user->fullname,
    //                 'is_online'       => $user->is_online,
    //                 'company'         => $user->company,
    //                 'transNo'         => $transNo,
    //             ]);

    //             // Save all questions
    //             foreach ($validated['question_text'] as $index => $questionText) {
    //                 Question::create([
    //                     'question_text' => $questionText,
    //                     'answer_type'   => $validated['answer_type'][$index] ?? 'yes',
    //                     'job_name'      => $validated['job_name'],
    //                     'role_code'     => $user->role_code,
    //                     'code'          => $user->code,
    //                     'fullname'      => $user->fullname,
    //                     'company'       => $user->company,
    //                     'transNo'       => $transNo,
    //                 ]);
    //             }
    //         }
    //         // ---------- UPDATE EXISTING JOB ----------
    //         else {
    //             $job = JobPosting::where('transNo', $transNo)->firstOrFail();

    //             // Update job fields
    //             $job->update([
    //                 'job_name'        => $validated['job_name'],
    //                 'job_position'    => $validated['job_position'],
    //                 'job_description' => $validated['job_description'],
    //                 'job_about'       => $validated['job_about'],
    //                 'qualification'   => $validated['qualification'],
    //                 'work_type'       => $validated['work_type'],
    //                 'location'        => $validated['location'],
    //                 'benefits'        => $validated['benefits'],

    //                 // ✅ SALARY RANGE
    //                 'min_salary'      => $validated['min_salary'],
    //                 'max_salary'      => $validated['max_salary'],

    //                 'job_image'       => $validated['job_image'] ?? $job->job_image,
    //             ]);

    //             // Update questions:
    //             // Remove old questions for this job
    //             Question::where('transNo', $transNo)->delete();

    //             // Insert new/updated questions
    //             foreach ($validated['question_text'] as $index => $questionText) {
    //                 Question::create([
    //                     'question_text' => $questionText,
    //                     'answer_type'   => $validated['answer_type'][$index] ?? 'yes',
    //                     'job_name'      => $validated['job_name'],
    //                     'role_code'     => $user->role_code,
    //                     'code'          => $user->code,
    //                     'fullname'      => $user->fullname,
    //                     'company'       => $user->company,
    //                     'transNo'       => $transNo,
    //                 ]);
    //             }
    //         }

    //         DB::commit();

    //         return response()->json([
    //             'success' => true,
    //             'message' => $transNo ? 'Job updated successfully' : 'Job saved successfully',
    //             'transNo' => $transNo,
    //         ], $transNo ? 200 : 201);
    //     } catch (\Illuminate\Validation\ValidationException $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'message' => 'Validation failed.',
    //             'success' => false,
    //             'errors'  => $e->errors(),
    //         ], 422);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'message' => 'Something went wrong while saving the job.',
    //             'success' => false,
    //             'error'   => $e->getMessage(),
    //         ], 500);
    //     }
    // }


    public function saveOrUpdateJobPosting(Request $request, $transNo = null)
    {
        try {
            $user = Auth::user();
            $validated = $request->validate([
                'job_name'        => 'required|string|max:255',
                'job_position'    => 'required|string|max:255',
                'job_description' => 'required|string',
                'job_about'       => 'required|string',
                'qualification'   => 'required|string',
                'work_type'       => 'required|string',
                'job_image'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'location'        => 'required|string|max:255',
                'benefits'        => 'required|string|max:255',
                'currency'        => 'required|string|max:10',
                'min_salary'      => 'required|numeric|min:0',
                'max_salary'      => 'required|numeric|min:0|gte:min_salary',
                'question_text'   => 'required|array|min:1',
                'question_text.*' => 'required|string|max:255',
                'answer_type'     => 'nullable|array',
                'answer_type.*'   => 'nullable|in:yes,no,general',
            ]);

            DB::beginTransaction();
            $isNewJob = empty($transNo);
            $job = $isNewJob ? null : JobPosting::where('transNo', $transNo)->firstOrFail();

            // -----------------------------------------------------
            // IMAGE UPLOAD (replace old image if new one uploaded)
            // -----------------------------------------------------
            if ($request->hasFile('job_image')) {
                if ($job && $job->job_image) {
                    Storage::disk('public')->delete(str_replace('/storage/', '', $job->job_image));
                }

                $file = $request->file('job_image');
                $folderPath = "uploads/{$user->code}/JobPosting/" . Str::uuid();
                $fileName = time() . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs($folderPath, $fileName, 'public');

                $validated['job_image'] = Storage::url($filePath);
            }

            // -----------------------------------------------------
            // CREATE OR UPDATE JOB
            // -----------------------------------------------------
            if ($isNewJob) {
                $lastTrans = JobPosting::orderByDesc('job_id')->first();
                $lastNumber = $lastTrans ? intval(substr($lastTrans->transNo, -6)) : 0;
                $transNo = "TR-" . str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);

                $job = JobPosting::create([
                    'job_name'        => $validated['job_name'],
                    'job_position'    => $validated['job_position'],
                    'job_description' => $validated['job_description'],
                    'job_about'       => $validated['job_about'],
                    'qualification'   => $validated['qualification'],
                    'work_type'       => $validated['work_type'],
                    'job_image'       => $validated['job_image'] ?? null,
                    'location'        => $validated['location'],
                    'benefits'        => $validated['benefits'],
                    'currency'        => $validated['currency'],
                    'min_salary'      => $validated['min_salary'],
                    'max_salary'      => $validated['max_salary'],
                    'code'            => $user->code,
                    'role_code'       => $user->role_code,
                    'fullname'        => $user->fullname,
                    'is_online'       => $user->is_online,
                    'company'         => $user->company,
                    'transNo'         => $transNo,
                ]);
            } else {
                $job->update([
                    'job_name'        => $validated['job_name'],
                    'job_position'    => $validated['job_position'],
                    'job_description' => $validated['job_description'],
                    'job_about'       => $validated['job_about'],
                    'qualification'   => $validated['qualification'],
                    'work_type'       => $validated['work_type'],
                    'location'        => $validated['location'],
                    'benefits'        => $validated['benefits'],
                    'currency'        => $validated['currency'],
                    'min_salary'      => $validated['min_salary'],
                    'max_salary'      => $validated['max_salary'],
                    'job_image'       => $validated['job_image'] ?? $job->job_image,
                ]);
            }

            // -----------------------------------------------------
            // QUESTIONS (replace all on every save)
            // -----------------------------------------------------
            Question::where('transNo', $transNo)->delete();
            foreach ($validated['question_text'] as $index => $questionText) {
                Question::create([
                    'question_text' => $questionText,
                    'answer_type'   => $validated['answer_type'][$index] ?? null,
                    'job_name'      => $validated['job_name'],
                    'role_code'     => $user->role_code,
                    'code'          => $user->code,
                    'company'       => $user->company,
                    'transNo'       => $transNo,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $isNewJob ? 'Job saved successfully' : 'Job updated successfully',
                'transNo' => $transNo,
            ], $isNewJob ? 201 : 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while saving the job.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function saveOrUpdateJobPostingOld(Request $request, $transNo = null)
    {
        try {
            $user = Auth::user();
            $validated = $request->validate([
                'job_name'        => 'required|string|max:255',
                'job_position'    => 'required|string|max:255',
                'job_description' => 'required|string',
                'job_about'       => 'required|string',
                'qualification'   => 'required|string',
                'work_type'       => 'required|string',
                'job_image'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'location'        => 'required|string|max:255',
                'benefits'        => 'required|string|max:255',

                // Salary
                'min_salary'      => 'required|numeric|min:0',
                'max_salary'      => 'required|numeric|min:0|gte:min_salary',

                // Questions
                'question_text'   => 'required|array|min:1',
                'question_text.*' => 'required|string|max:255',

                // Accept answer_type from frontend,
                // but DO NOT save it because the database column does not exist.
                'answer_type'     => 'nullable|array',
                'answer_type.*'   => 'nullable|in:yes,no,general',
            ]);

            DB::beginTransaction();

            // ---------------------------------------------------------
            // DETERMINE CREATE OR UPDATE
            // ---------------------------------------------------------
            $isNewJob = empty($transNo);

            // ---------------------------------------------------------
            // UPLOAD JOB IMAGE
            // ---------------------------------------------------------
            if ($request->hasFile('job_image')) {

                $file = $request->file('job_image');

                $uuid = Str::uuid();

                $folderPath = "uploads/{$user->code}/JobPosting/{$uuid}";

                $fileName = time() . '.' . $file->getClientOriginalExtension();

                $filePath = $file->storeAs(
                    $folderPath,
                    $fileName,
                    'public'
                );

                $validated['job_image'] = "/storage/app/public/" . $filePath;
            }

            // =========================================================
            // CREATE NEW JOB
            // =========================================================
            if ($isNewJob) {

                // Get latest transaction number
                $lastTrans = JobPosting::orderByDesc('job_id')->first();

                $lastNumber = 0;

                if ($lastTrans && !empty($lastTrans->transNo)) {
                    $lastNumber = intval(
                        substr($lastTrans->transNo, -6)
                    );
                }

                $transNo = "TR-" . str_pad(
                    $lastNumber + 1,
                    6,
                    '0',
                    STR_PAD_LEFT
                );

                // -----------------------------------------------------
                // CREATE JOB
                // -----------------------------------------------------
                $job = JobPosting::create([
                    'job_name'        => $validated['job_name'],
                    'job_position'    => $validated['job_position'],
                    'job_description' => $validated['job_description'],
                    'job_about'       => $validated['job_about'],
                    'qualification'   => $validated['qualification'],
                    'work_type'       => $validated['work_type'],
                    'job_image'       => $validated['job_image'] ?? null,
                    'location'        => $validated['location'],
                    'benefits'        => $validated['benefits'],

                    // Salary
                    'min_salary'      => $validated['min_salary'],
                    'max_salary'      => $validated['max_salary'],

                    // User information
                    'code'            => $user->code,
                    'role_code'       => $user->role_code,
                    'fullname'        => $user->fullname,
                    'is_online'       => $user->is_online,
                    'company'         => $user->company,

                    // Transaction
                    'transNo'         => $transNo,
                ]);

                // -----------------------------------------------------
                // SAVE QUESTIONS
                // -----------------------------------------------------
                foreach ($validated['question_text'] as $index => $questionText) {

                    Question::create([
                        'question_text' => $questionText,

                        // IMPORTANT:
                        // Do NOT include answer_type here because
                        // applied_questions does not have this column.

                        'job_name'      => $validated['job_name'],
                        'role_code'     => $user->role_code,
                        'code'          => $user->code,
                        'company'       => $user->company,
                        'transNo'       => $transNo,
                    ]);
                }
            }

            // =========================================================
            // UPDATE EXISTING JOB
            // =========================================================
            else {

                $job = JobPosting::where('transNo', $transNo)
                    ->firstOrFail();

                // -----------------------------------------------------
                // UPDATE JOB
                // -----------------------------------------------------
                $job->update([
                    'job_name'        => $validated['job_name'],
                    'job_position'    => $validated['job_position'],
                    'job_description' => $validated['job_description'],
                    'job_about'       => $validated['job_about'],
                    'qualification'   => $validated['qualification'],
                    'work_type'       => $validated['work_type'],
                    'location'        => $validated['location'],
                    'benefits'        => $validated['benefits'],

                    // Salary
                    'min_salary'      => $validated['min_salary'],
                    'max_salary'      => $validated['max_salary'],

                    // Keep old image if no new image uploaded
                    'job_image'       => $validated['job_image']
                        ?? $job->job_image,
                ]);

                // -----------------------------------------------------
                // DELETE OLD QUESTIONS
                // -----------------------------------------------------
                Question::where('transNo', $transNo)->delete();

                // -----------------------------------------------------
                // INSERT UPDATED QUESTIONS
                // -----------------------------------------------------
                foreach ($validated['question_text'] as $index => $questionText) {

                    Question::create([
                        'question_text' => $questionText,

                        // Do NOT include answer_type.
                        // The applied_questions table does not have it.

                        'job_name'      => $validated['job_name'],
                        'role_code'     => $user->role_code,
                        'code'          => $user->code,
                        'company'       => $user->company,
                        'transNo'       => $transNo,
                    ]);
                }
            }

            // ---------------------------------------------------------
            // COMMIT
            // ---------------------------------------------------------
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $isNewJob
                    ? 'Job saved successfully'
                    : 'Job updated successfully',
                'transNo' => $transNo,
            ], $isNewJob ? 201 : 200);
        } catch (\Illuminate\Validation\ValidationException $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while saving the job.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }




    public function getApplicationScore($transNo)
    {
        $rows = Question::where('transNo', $transNo)->get();

        if ($rows->isEmpty()) {
            return response()->json(['message' => 'Record not found'], 404);
        }

        $answers = $rows->map(function ($item) {
            $ansText = strtolower($item->answer_text);
            $ansType = strtolower($item->answer_type);

            // --- SCORING LOGIC ---
            $scoreValue = 0;

            if ($ansText === 'yes') {
                $scoreValue = 1.0;
            } elseif ($ansText === 'general' || $ansType === 'general') {
                $scoreValue = 0.5; // "Medium" Score
            } else {
                $scoreValue = 0.0;
            }

            return [
                'question_text' => $item->question_text,
                'answer_text'   => $item->answer_text ?? 'N/A',
                'numeric_score' => $scoreValue,
                'is_correct'    => $ansText === 'yes'
            ];
        });

        $totalScore = $answers->sum('numeric_score');
        $totalQuestions = $answers->count();

        return response()->json([
            'answers' => $answers,
            'summary' => [
                'total_score'    => $totalScore,
                'total_possible' => $totalQuestions,
                'percentage'     => $totalQuestions > 0 ? round(($totalScore / $totalQuestions) * 100) : 0
            ]
        ], 200);
    }


    public function getApplicationScorexx($transNo)
    {
        // 1. Fetch all rows for this specific transaction
        $rows = Question::where('transNo', $transNo)->get();

        if ($rows->isEmpty()) {
            return response()->json(['message' => 'No data found'], 404);
        }

        // 2. Map data to the format your Angular Component expects
        $answers = $rows->map(function ($item) {
            // Logic: 'yes' is correct, everything else (no, null, general) is false
            $isCorrect = strtolower($item->answer_text) === 'yes';

            return [
                'question_text' => $item->question_text,
                'answer_text'   => $item->answer_text ?? 'N/A',
                'correct'       => $isCorrect, // This drives your Angular this.score
                'answer_type'   => $item->answer_type
            ];
        });

        // 3. Calculate Summary for the Header
        $total = $answers->count();
        $correctCount = $answers->where('correct', true)->count();
        $percentage = $total > 0 ? round(($correctCount / $total) * 100) : 0;

        return response()->json([
            'user' => [
                'job_name' => $rows->first()->job_name,
                'company'  => $rows->first()->company,
                'status'   => $rows->first()->status,
            ],
            'scoring' => [
                'totalQuestions' => $total,
                'score' => $correctCount,
                'percentage' => $percentage
            ],
            'answers' => $answers,
            'resumes' => [] // Add logic to fetch resume URLs here if needed
        ], 200);
    }

    public function saveJobPostingss(Request $request)
    {
        try {
            $user = Auth::user();

            $lastTrans = JobPosting::orderByDesc('job_id')->first();
            $lastNumber = $lastTrans ? intval(substr($lastTrans->transNo, -6)) : 0;
            $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);

            $transNo = "TR-$newNumber";


            // ✅ Validate request
            $validated = $request->validate([
                'job_name'        => 'required|string|max:255',
                'job_position'    => 'required|string|max:255',
                'job_description' => 'required|string',
                'job_about'       => 'required|string',
                'qualification'   => 'required|string',
                'work_type'       => 'required|string',
                'job_image'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'location'        => 'required|string|max:255',
                'benefits'        => 'required|string|max:255',
                'question_text'   => 'required|array|min:1',         // 🔑 must be an array
                'question_text.*' => 'required|string|max:255',      // 🔑 each item must be string
            ]);

            // ✅ Handle file upload
            if ($request->hasFile('job_image')) {
                $file = $request->file('job_image');
                $uuid = Str::uuid();
                $folderPath = "uploads/{$user->code}/JobPosting/{$uuid}";
                $fileName = time() . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs($folderPath, $fileName, 'public');
                $validated['job_image'] = "/storage/app/public/" . $filePath;
            }

            DB::beginTransaction();

            // ✅ Save Job Posting
            $job = JobPosting::create([
                'job_name'        => $validated['job_name'],
                'job_position'    => $validated['job_position'],
                'job_description' => $validated['job_description'],
                'job_about'       => $validated['job_about'],
                'qualification'   => $validated['qualification'],
                'work_type'       => $validated['work_type'],
                'job_image'       => $validated['job_image'] ?? null,
                'location'        => $validated['location'],
                'benefits'        => $validated['benefits'],
                'code'            => $user->code,
                'role_code'       => $user->role_code,
                'fullname'        => $user->fullname,
                'is_online'       => $user->is_online,
                'company'         => $user->company,
                'transNo'        => $transNo,
            ]);

            // ✅ Save multiple Questions
            foreach ($validated['question_text'] as $index => $questionText) {
                Question::create([
                    'question_text' => $questionText,
                    'answer_type'   => $validated['answer_type'][$index] ?? 'yes',
                    // 'job_id'        => $job->id,
                    'job_name'      => $validated['job_name'],
                    'role_code'     => $user->role_code,
                    'code'          => $user->code,
                    'fullname'      => $user->fullname,
                    'company'       => $user->company,
                    'transNo'      => $transNo,
                ]);
            }

            DB::commit();

            return response()->json([
                'success'  => true,
                'message'  => 'Job saved successfully',
                'transNo' => $transNo,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Validation failed.',
                'success' => false,
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Something went wrong while saving the job.',
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    public function updateJobPosting(Request $request, $transNo)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.',
            ], 401);
        }

        $currentUserCode = Auth::user()->code;
        $role_code = Auth::user()->role_code;
        $fname = Auth::user()->fname;
        $is_online = Auth::user()->is_online;
        // ✅ Validation
        $validator = Validator::make($request->all(), [
            'job_name'        => 'required|string|max:255',
            'job_position'    => 'required|string|max:255',
            'job_description' => 'required|string',
            'job_about'       => 'required|string',
            'qualification'   => 'required|string',
            'work_type'       => 'required|string',
            //  'comp_name'       => 'required|string|max:255',
            //    'comp_description'=> 'required|string',
            'job_image'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            // ✅ Find only if belongs to current user
            $job = JobPosting::where('transNo', $transNo)
                ->where('code', $currentUserCode)
                ->first();

            if (!$job) {
                return response()->json([
                    'success' => false,
                    'message' => 'Job posting not found.',
                ], 404);
            }

            // ✅ Handle image upload
            if ($request->hasFile('job_image')) {
                $file = $request->file('job_image');
                $uuid = Str::uuid();
                $folderPath = "uploads/{$currentUserCode}/JobPosting/{$uuid}";
                $fileName = time() . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs($folderPath, $fileName, 'public');
                $job->job_image = "/storage/app/public/" . $filePath;
            }

            // ✅ Update fields
            $job->job_name        = $request->job_name;
            $job->job_position    = $request->job_position;
            $job->job_description = $request->job_description;
            $job->job_about       = $request->job_about;
            $job->qualification   = $request->qualification;
            $job->work_type       = $request->work_type;
            $job->comp_name       = $request->comp_name;
            $job->comp_description = $request->comp_description;
            $job->code            = $currentUserCode;
            $job->role_code       = $role_code;
            $job->fname           = $fname;
            $job->is_online       = $is_online;
            $job->updated_at      = now();

            $job->save();

            return response()->json([
                'success' => true,
                'message' => 'Jobs updated successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update job posting.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    public function getJobPostingsByCode()
    {
        try {
            $code = Auth::user()->code;

            $jobs = JobPosting::where('code', $code)
                ->orderBy('created_at', 'asc')
                ->get();

            // Get all transNo values from the jobs collection
            $transNos = $jobs->pluck('transNo');

            $questions = Question::where('code', $code)
                ->whereIn('transNo', $transNos)
                ->orderBy('created_at', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'jobs' => $jobs,
                'questions' => $questions
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while fetching job postings.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function getJobPostingByTransNo($transNo)
    {
        try {
            // ✅ Fetch job posting by transNo
            $job = DB::table('jobposting')
                ->where('transNo', $transNo)
                ->first();

            if (!$job) {
                return response()->json([
                    'success' => false,
                    'message' => 'Job posting not found.',
                ], 404);
            }

            // ✅ Fetch related questions (same transNo)
            $questions = DB::table('applied_questions')
                ->where('transNo', $transNo)
                ->get();

            return response()->json([
                'success'   => true,
                'job'       => $job,
                'questions' => $questions,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    public function deleteJobPosting($transNo)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized.',
                ], 401);
            }

            // Find job by transNo + owner code
            $job = JobPosting::where('transNo', $transNo)
                ->where('code', $user->code)
                ->first();

            if (!$job) {
                return response()->json([
                    'success' => false,
                    'message' => 'Job not found or you are not authorized to delete it.',
                ], 404);
            }

            DB::beginTransaction();
            // Delete job image from storage if exists
            if ($job->job_image && Storage::exists($job->job_image)) {
                Storage::delete($job->job_image);
            }

            // Delete related applied questions
            DB::table('applied_questions')
                ->where('transNo', $transNo)
                ->delete();

            $job->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Job deleted successfully.',
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the job.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
