<?php

namespace App\Http\Controllers\Jobs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Jobs\SavedJob;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SavedJobController extends Controller
{
    // Toggle save/unsave
    public function saveJobs(Request $request)
    {
        $request->validate([
            'job_id' => 'required|integer',
        ]);
        $user = Auth::user();
        $existing = SavedJob::where('code', $user->code)
            ->where('job_id', $request->input('job_id'))
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json(['saved' => false]);
        }

        SavedJob::create([
            'code'   => $user->code,
            'job_id' => $request->input('job_id'),
        ]);

        return response()->json(['saved' => true]);
    }

    public function getSaveJobs()
    {
        $user = Auth::user();
        $jobs = DB::table('saved_jobs')
            ->leftJoin('jobposting', 'saved_jobs.job_id', '=', 'jobposting.job_id')
            ->where('saved_jobs.code',  $user->code)
            ->where('saved_jobs.recordstatus', 'Active')
            ->orderByDesc('saved_jobs.created_at')
            ->select(
                'saved_jobs.id as saved_job_id',
                'saved_jobs.code',
                'saved_jobs.job_id',
                'saved_jobs.recordstatus',
                'saved_jobs.created_at',
                'jobposting.job_name',
                'jobposting.company',
                'jobposting.work_type',
                'jobposting.location',
                'jobposting.job_image'
            )
            ->get();

        return response()->json([
            'success' => true,
            'data' => $jobs
        ]);
    }
}
