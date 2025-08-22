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
            // Store in storage/app/public/...
            $filePath = $file->storeAs($folderPath, $fileName, 'public');
            // Save with full "storage/app/public/..." path
            $validated['job_image'] = "storage/app/public/" . $filePath;
        }

        $job = JobPosting::create($validated);

        return response()->json([
            'message' => 'Job saved successfully!',
            'data' => $job
        ], 201);
    }


























}
