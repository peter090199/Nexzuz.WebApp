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
    public function saveJobPosting(Request $request)
    {
        try {
            $userCode = Auth::user()->code;

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
                $folderPath = "uploads/{$userCode}/JobPosting/{$uuid}";
                $fileName = time() . '.' . $file->getClientOriginalExtension();

                // Store file in storage/app/public
                $filePath = $file->storeAs($folderPath, $fileName, 'public');

                // Save public path (accessible via /storage symlink)
                $validated['job_image'] = "/storage/" . $filePath;
            }

            $job = JobPosting::create($validated);

            return response()->json([
                'message' => 'Job saved successfully!',
                'data' => $job
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong while saving the job.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

























}
