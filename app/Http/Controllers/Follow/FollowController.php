<?php

namespace App\Http\Controllers\Follow;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\File; 
use Illuminate\Support\Facades\Validator;
use App\Models\Post;
use App\Models\Attachmentpost;
use App\Models\Resource;
use App\Models\Userprofile;
use Illuminate\Support\Str;
use DB;

class FollowController extends Controller
{
    /**
     * Display a listing of the resource.
     */
        // follower_code	The user who follows (that's you)
        // following_code	The user who is being followed
    public function index()
    {
        //
        $data = DB::select('SELECT (select getUserprofilepic(p.code AS profile_pic, (select getFullname(p.code)) AS fullname,
        p.posts_uuid, p.caption, p.status, p.created_at, p.updated_at FROM posts AS p
        LEFT JOIN follows AS f ON f.following_code = p.code AND f.follower_code = ?
        WHERE p.status = 1
        AND (f.follower_code IS NOT NULL OR p.code = ?)
        ORDER BY p.created_at DESC', [Auth::user()->code, Auth::user()->code]);

        $result = [];
        for($i = 0; $i < count($data); $i++){


            $result [$i]= [
                "profile_pic" => $data[$i]->profile_pic,
                "Fullname" => $data[$i]->fullname,
                "posts_uuid" => $data[$i]->posts_uuid,
                "caption" => $data[$i]->caption,
                "status" => $data[$i]->status,
                "caption" => $data[$i]->caption,
            ];
        }

        return $result;

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
