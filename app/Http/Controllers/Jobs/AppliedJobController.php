<?php

namespace App\Http\Controllers\Jobs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\File; 
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB; 
use App\Models\Jobs\AppliedJobs;
use App\Models\Jobs\AppliedResumes;
use Illuminate\Support\Facades\Mail;
use App\Mail\AppliedStatusUpdated;
use App\Http\Controllers\ChatController;



class AppliedJobController extends Controller
{
    public function saveAppliedJob(Request $request)
    {
        try {
            $user = Auth::user();

            // ✅ Validate request
            $validated = $request->validate([
                'job_name'      => 'required|string|max:255',
                'email'         => 'required|string|email|max:255',
                'country_code'  => 'required|string|max:10',
                'phone_number'  => 'required|string|max:20',
                'transNo'       => 'required|string|max:50',
                'resume_pdf'    => 'required|file|mimes:pdf|max:2048', // PDF only
                'answers'       => 'required|array',                   // expect multiple answers
                'answers.*.question_id' => 'required|integer',
                'answers.*.answer_text' => 'required|string',
            ]);

            DB::beginTransaction();

            // ✅ Save file
            $resumePath = null;
            if ($request->hasFile('resume_pdf')) {
                $file = $request->file('resume_pdf');
                $uuid = Str::uuid();
                $folderPath = "uploads/{$user->code}/AppliedJobsResume/{$uuid}";
                $fileName = time() . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs($folderPath, $fileName, 'public');
                $resumePath = "/storage/" . $filePath;
            }

            // ✅ Save Applied Job
            $job = AppliedJobs::create([
                'transNo'       => $validated['transNo'],
                'job_name'      => $validated['job_name'],
                'email'         => $validated['email'],
                'country_code'  => $validated['country_code'],
                'phone_number'  => $validated['phone_number'],
                'code'          => $user->code,
                'role_code'     => $user->role_code,
                'fullname'      => $user->fullname,
            ]);

            // ✅ Save related Resume record
            AppliedResumes::create([
                'transNo'       => $validated['transNo'],
                'resume_pdf'    => $resumePath,
                'job_name'      => $validated['job_name'],
                'role_code'     => $user->role_code,
                'code'          => $user->code,
                'fullname'      => $user->fullname,
                'company'       => $user->company,
            ]);

            // ✅ Update multiple answers
            foreach ($validated['answers'] as $answer) {
                DB::table('applied_questions')
                    ->where('transNo', $validated['transNo'])
                    ->where('question_id', $answer['question_id'])
                    ->update([
                        'answer_text' => $answer['answer_text'],
                        'updated_at'  => now(),
                    ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Applied Job saved successfully',
                'transNo' => $validated['transNo'],
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
                'message' => 'Something went wrong while saving the job application.',
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function getAppliedJob()
    {
        $user = Auth::user();

        $results = DB::table('applied_jobs as aj')
            ->leftJoin('jobPosting as jp', 'aj.transNo', '=', 'jp.transNo')
            ->select(
                'jp.transNo',
                'aj.code',
                'aj.role_code',
                'aj.applied_id',
                'aj.fullname',
                'aj.job_name',
                'aj.email',
                'aj.country_code',
                'aj.phone_number',
                'aj.applied_status'
            )
            ->where('jp.code', $user->code) // or use email if your table doesn't have user_id
            ->get();

        if ($results->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No applied jobs found for this user.',
                'data' => []
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $results
        ]);
    }


    public function updateAppliedStatus(Request $request, $transNo)
    {
        $user = Auth::user();
        $request->validate([
            'status' => 'required|string|max:50',
        ]);

        $status = $request->input('status');
        $updated = DB::table('applied_jobs')
            ->where('transNo', $transNo)
            ->update(['applied_status' => $status]);

        if (!$updated) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update applied status. Record may not exist or unauthorized.'
            ]);
        }

        // Fetch updated job details
        $job = DB::table('applied_jobs')->where('transNo', $transNo)->first();

        // --- Send email notification ---
        if ($job && $job->email) {
            Mail::to($job->email)->send(new AppliedStatusUpdated($job, $status));
        }

        // --- Get user ID from users table using code ---
        $receiver = DB::table('users')
            ->where('code', $job->code)
            ->select('id')
            ->first();

        if ($receiver) {
            $chatController = new ChatController();

            $requestMessage = new Request([
                'receiver_id' => $receiver->id,
                'message' => "Your application for '{$job->job_name}' has been updated to '{$status}'."
            ]);

            $response = $chatController->sendMessage($requestMessage);
        }

        return response()->json([
            'success' => true,
            'message' => "Applied status updated to '{$status}', email sent, and message notification sent."
        ]);
    }



}
