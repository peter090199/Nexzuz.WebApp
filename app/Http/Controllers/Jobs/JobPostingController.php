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

class JobPostingController extends Controller
{
    public function saveJobPostingxx(Request $request)
    {
        try {
            $user = Auth::user();
            $transNo = 'TR-' . strtoupper(uniqid());

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
                'question_text'   => 'required|string|max:255',
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
                'trans_no'        => $transNo,
            ]);

            // ✅ Save Question tied to the Job
            Question::create([
                'question_text' => $validated['question_text'],
                'job_name'      => $validated['job_name'],
                'role_code'     => $user->role_code,
                'code'          => $user->code,
                'fullname'      => $user->fullname,
                'company'       => $user->company,
                'transNo'      => $transNo,
            ]);

            DB::commit();

            return response()->json([
                'success'  => true,
                'message'  => 'Job and Question saved successfully!',
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
                'message' => 'Something went wrong while saving.',
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function saveJobPostingx1(Request $request)
    {
        
        try{
        $role_code = Auth::user()->role_code;
        $code = Auth::user()->code;
        $fullname = Auth::user()->fullname;
        $company = Auth::user()->company;
        $is_online = Auth::user()->is_online;
        $transNo = 'TR-' . strtoupper(uniqid());
       
        $validated = $request->validate([
            'job_name' => 'required|string|max:255',
            'job_position' => 'required|string|max:255',
            'job_description' => 'required|string',
            'job_about' => 'required|string',
            'qualification' => 'required|string',
            'work_type' => 'required|string',
            'job_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'location' => 'required|string|max:255',
            'benefits' => 'required|string|max:255',
            'question_text'   => 'required|string|max:255', // merged validation
        ]);

        if ($request->hasFile('job_image')) {
            $file = $request->file('job_image');
            $uuid = Str::uuid();
            $folderPath = "uploads/{$code}/JobPosting/{$uuid}";
            $fileName = time() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs($folderPath, $fileName, 'public');
            $validated['job_image'] = "/storage/app/public/" . $filePath;
        }
            $validated['code'] = $code;
            $validated['role_code'] = $role_code;
            $validated['fullname'] = $fullname;
            $validated['is_online'] = $is_online;
            $validated['company'] = $company;
            $validated['trans_no']  = $transNo;

            $job = JobPosting::create($validated);

           // $job = $this->questionController->addQuestions($request);
            $question = Question::create([
                        'question_text' => $validatedJob['question_text'],
                        'job_name'      => $validatedJob['job_name'],
                        'role_code'     => $user->role_code,
                        'code'          => $user->code,
                        'fullname'      => $user->fullname,
                        'company'       => $user->company,
                        'transNo'      => $transNo, // attach transNo to question also
                    ]);

                    
           return response()->json([
                'success'  => true,
                'message'  => 'Job and Question saved successfully',
                'transNo' => $transNo,   // 🔑 return transaction number
            ], 201);


            } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                 'success'   => false,
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong while saving the job.',
                'success'   => false,
                'error'   => $e->getMessage()
            ], 500);
        }
    }



    public function saveJobPosting(Request $request)
    {
        try {
            $user = Auth::user();
            $transNo = 'TR-' . strtoupper(uniqid());

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
                'trans_no'        => $transNo,
            ]);

            // ✅ Save multiple Questions
            foreach ($validated['question_text'] as $questionText) {
                Question::create([
                    'question_text' => $questionText,
                    'job_id'        => $job->id,
                    'job_name'      => $validated['job_name'],
                    'role_code'     => $user->role_code,
                    'code'          => $user->code,
                    'fullname'      => $user->fullname,
                    'company'       => $user->company,
                    'trans_no'      => $transNo,
                ]);
            }

            DB::commit();

            return response()->json([
                'success'  => true,
                'message'  => 'Job and Questions saved successfully',
                'trans_no' => $transNo,
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

    
    public function updateJobPostingX(Request $request, $id)
    {
        try {
            $role_code = Auth::user()->role_code;
            $code = Auth::user()->code;

            $job = JobPosting::where('id', $id)
                ->where('code', $code) // ✅ only allow updating own jobs
                ->firstOrFail();

            $validated = $request->validate([
                'job_name' => 'sometimes|required|string|max:255',
                'job_position' => 'sometimes|required|string|max:255',
                'job_description' => 'sometimes|required|string',
                'job_about' => 'sometimes|required|string',
                'qualification' => 'sometimes|required|string',
                'work_type' => 'sometimes|required|string',
                'comp_name' => 'sometimes|required|string|max:255',
                'comp_description' => 'sometimes|required|string',
                'job_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            ]);

            if ($request->hasFile('job_image')) {
                $file = $request->file('job_image');
                $uuid = Str::uuid();
                $folderPath = "uploads/{$code}/JobPosting/{$uuid}";
                $fileName = time() . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs($folderPath, $fileName, 'public');
                $validated['job_image'] = "/storage/app/public/" . $filePath;
            }
                $validated['code'] = $code;
                $validated['role_code'] = $role_code;

                // ✅ Update and reload
                $job->update($validated);
                $job->refresh();

                return response()->json([
                    'message' => 'Job updated successfully!',
                    'success' => true,
                ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'success' => false,
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong while updating the job.',
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateJobPosting(Request $request, $id)
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
            'comp_name'       => 'required|string|max:255',
            'comp_description'=> 'required|string',
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
            $job = JobPosting::where('id', $id)
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
            $job->comp_description= $request->comp_description;
            $job->code            = $currentUserCode;
            $job->role_code       = $role_code;
            $job->fname           = $fname;
            $job->is_online       = $is_online;
            $job->updated_at      = now();

            $job->save();

            return response()->json([
                'success' => true,
                'message' => 'Job posting updated successfully.',
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
            ->orderBy('job_name', 'asc')
            ->get();

            return response()->json([
                'success' => true,
                'jobs' => $jobs
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while fetching job postings.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteJobPosting($id)
    {
        try {
            $code = Auth::user()->code;

            $job = JobPosting::where('id', $id)
                ->where('code', $code) // ✅ only delete own jobs
                ->firstOrFail();

            $job->delete();

            return response()->json([
                'success' => true,
                'message' => 'Job deleted successfully!',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while deleting the job.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


























}
