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
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $jobs
        ]);
    }

    public function getActiveJobsByCode()
    {
        $currentUserCode = Auth::user()->code;

        $jobs = DB::table('jobPosting')  
            ->where('recordstatus', 'active')
            ->where('code', $currentUserCode)  // FILTER BY OWNER
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $jobs
        ]);
    }



}
