<?php

namespace App\Http\Controllers\Jobs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JobListController extends Controller
{
    public function getActiveJobs()
    {
        $jobs = DB::table('jobPosting')
            ->select(
                'id',
                'code',
                'role_code',
                'job_name',
                'job_image',
                'job_position',
                'comp_name',
                'work_type',
                'recordstatus',
                'created_at'
            )
            ->where('recordstatus', 'active')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $jobs
        ]);
    }



}
