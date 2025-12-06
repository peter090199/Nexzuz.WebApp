<?php

namespace App\Http\Controllers\Jobs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class JobListController extends Controller
{
    public function getActiveJobs()
    {
            $jobs = DB::table('jobPosting')
            ->where('recordstatus', 'active')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $jobs
        ]);
    }

    public function getActiveJobsByCode($code)
    {
        $jobs = DB::table('jobPosting')
            ->where('recordstatus', 'active')
            ->where('code', $code) 
            ->orderByDesc('created_at') 
            ->get();

        return response()->json([
            'success' => true,
            'data' => $jobs
        ]);
    }



}
