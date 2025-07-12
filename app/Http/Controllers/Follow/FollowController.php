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
            $data = DB::select(' SELECT 
                    (SELECT getUserprofilepic(p.code)) AS profile_pic,
                    (SELECT getFullname(p.code)) AS fullname,
                    p.posts_uuid,
                    p.caption,
                    p.status,
                    p.created_at,
                    p.updated_at,
                    p.code AS post_owner
                FROM posts AS p
                LEFT JOIN follows AS f 
                    ON f.following_code = p.code AND f.follower_code = ? AND f.follow_status = "accepted"
                WHERE p.status = 1
                AND (f.follower_code IS NOT NULL OR p.code = ?)
                ORDER BY p.created_at DESC
            ', [Auth::user()->code, Auth::user()->code]);

            $result = [];
            for ($i = 0; $i < count($data); $i++) {

                $attachements = DB::select('SELECT * FROM attachmentposts 
                    WHERE posts_uuid = ? AND (status = 1 OR ? = (SELECT code FROM posts WHERE posts_uuid = ?))', [
                    $data[$i]->posts_uuid, Auth::user()->code, $data[$i]->posts_uuid
                ]);
                $result[$i] = [
                    "profile_pic" => $data[$i]->profile_pic,
                    "Fullname" => $data[$i]->fullname,
                    "posts_uuid" => $data[$i]->posts_uuid,
                    "caption" => $data[$i]->caption,
                    "status" => $data[$i]->status,
                    "created_at" => $data[$i]->created_at,
                    "updated_at" => $data[$i]->updated_at,
                    "posts" => $attachements,
                ];
            }
            return response()->json($result);
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
        if ($id == Auth::user()->code) {
            return response()->json(['status' => false, 'message' => 'Cannot follow yourself'], 400);
        }

        DB::beginTransaction();

        try {
            $followerCode = Auth::user()->code;

            $exists = DB::select('SELECT * FROM follows WHERE follower_code = ? AND following_code = ?', [
                $followerCode,
                $id,
            ]);

            if (count($exists) > 0) {
                // Delete (cancel request or unfollow)
                DB::delete('DELETE FROM follows WHERE follower_code = ? AND following_code = ?', [
                    $followerCode,
                    $id,
                ]);
                $message = 'Follow request cancelled or unfollowed';
                $followStatus = 'cancelled';
            } else {
                // Insert with 'pending' status
                DB::insert('INSERT INTO follows (follower_code, following_code, follow_status, created_at) VALUES (?, ?, ?, NOW())', [
                    $followerCode,
                    $id,
                    'pending'
                ]);
                $message = 'Follow request sent';
                $followStatus = 'pending';
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => $message,
                'follow_status' => $followStatus
        ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'Operation failed', 'error' => $e->getMessage()], 500);
        }
    }


    // public function update(Request $request, string $id)
    // {
    //     if ($id == Auth::user()->code) {
    //         return response()->json(['status' => false, 'message' => 'Cannot follow yourself'], 400);
    //     }
    
    //     DB::beginTransaction();
    
    //     try {
    //         $exists = DB::select('SELECT * FROM follows WHERE follower_code = ? AND following_code = ?', [
    //             Auth::user()->code,
    //             $id,
    //         ]);
    
    //         if (count($exists) > 0) {
    //             // Delete (unfollow)
    //             DB::delete('DELETE FROM follows WHERE follower_code = ? AND following_code = ?', [
    //                 Auth::user()->code,
    //                 $id,
    //             ]);
    //             $message = 'Unfollowed';
    //         } else {
    //             // Insert (follow)
    //             DB::insert('INSERT INTO follows (follower_code, following_code, created_at) VALUES (?, ?, NOW())', [
    //                 Auth::user()->code,
    //                 $id,
    //             ]);
    //             $message = 'Followed';
    //         }
    
    //         DB::commit();
    
    //         return response()->json(['status' => true, 'message' => $message]);
    
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json(['status' => false, 'message' => 'Operation failed', 'error' => $e->getMessage()], 500);
    //     }
    // }
    

    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
