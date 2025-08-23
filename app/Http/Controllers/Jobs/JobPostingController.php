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

class JobPostingController extends Controller
{
  
    // public function saveOrUpdateJobPosting(Request $request, $id = null)
    // {
    //     try {
    //         $role_code = Auth::user()->role_code;
    //         $code = Auth::user()->code;

    //         $validated = $request->validate([
    //             'job_name' => 'required|string|max:255',
    //             'job_position' => 'required|string|max:255',
    //             'job_description' => 'required|string',
    //             'job_about' => 'required|string',
    //             'qualification' => 'required|string',
    //             'work_type' => 'required|string',
    //             'comp_name' => 'required|string|max:255',
    //             'comp_description' => 'required|string',
    //             'job_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
    //         ]);

    //         // Handle file upload
    //         if ($request->hasFile('job_image')) {
    //             $file = $request->file('job_image');
    //             $uuid = Str::uuid();
    //             $folderPath = "uploads/{$code}/JobPosting/{$uuid}";
    //             $fileName = time() . '.' . $file->getClientOriginalExtension();
    //             $filePath = $file->storeAs($folderPath, $fileName, 'public');
    //             $validated['job_image'] = "/storage/app/public/" . $filePath;
    //         }

    //         $validated['code'] = $code;
    //         $validated['role_code'] = $role_code;

    //         if ($id) {
    //             // ✅ UPDATE
    //             $job = JobPosting::find($id);
    //             $job->update($validated);

    //             return response()->json([
    //                 'message' => 'Job updated successfully!',
    //                 'success' => true,
    //             ], 200);
    //         } else {
    //             // ✅ CREATE
    //             JobPosting::create($validated);

    //             return response()->json([
    //                 'message' => 'Job saved successfully!',
    //                 'success' => true,
    //             ], 201);
    //         }

    //     } catch (\Illuminate\Validation\ValidationException $e) {
    //         return response()->json([
    //             'message' => 'Validation failed.',
    //             'success' => false,
    //             'errors' => $e->errors(),
    //         ], 422);

    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'message' => 'Something went wrong.',
    //             'success' => false,
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }


    public function saveJobPosting(Request $request)
    {
        try{
        $role_code = Auth::user()->role_code;
        $code = Auth::user()->code;
        $validated = $request->validate([
            'job_name' => 'required|string|max:255',
            'job_position' => 'required|string|max:255',
            'job_description' => 'required|string',
            'job_about' => 'required|string',
            'qualification' => 'required|string',
            'work_type' => 'required|string',
            'comp_name' => 'required|string|max:255',
            'comp_description' => 'required|string',
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

            $job = JobPosting::create($validated);

            return response()->json([
                'message' => 'Job saved successfully!',
                'success'   => true,
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
            $code = Auth::user()->code; // ✅ only get jobs of logged-in user

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
