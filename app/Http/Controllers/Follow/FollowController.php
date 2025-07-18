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
        // follower_code	The user who follows (that's you)
        // following_code	The user who is being followed
        public function index()
        {
            $currentUserCode = Auth::user()->code;

            $data = DB::select('
                SELECT 
                    (SELECT getUserprofilepic(p.code)) AS profile_pic,
                    (SELECT getFullname(p.code)) AS fullname,
                    p.posts_uuid,
                    p.caption,
                    p.status,
                    p.created_at,
                    p.updated_at,
                    p.code AS post_owner
                FROM posts AS p
                LEFT JOIN follows AS f1 
                    ON f1.following_code = p.code AND f1.follower_code = ? AND f1.follow_status = "accepted"
                LEFT JOIN follows AS f2 
                    ON f2.follower_code = p.code AND f2.following_code = ? AND f2.follow_status = "accepted"
                WHERE p.status = 1
                AND (
                    f1.follower_code IS NOT NULL
                    OR f2.following_code IS NOT NULL
                    OR p.code = ?
                )
                ORDER BY p.created_at DESC
            ', [$currentUserCode, $currentUserCode, $currentUserCode]);

            $result = [];

            foreach ($data as $post) {
                $attachments = DB::select('
                    SELECT * FROM attachmentposts 
                    WHERE posts_uuid = ?
                    AND (status = 1 OR ? = (SELECT code FROM posts WHERE posts_uuid = ?))
                ', [$post->posts_uuid, $currentUserCode, $post->posts_uuid]);

                $result[] = [
                    "profile_pic" => $post->profile_pic,
                    "Fullname" => $post->fullname,
                    "posts_uuid" => $post->posts_uuid,
                    "caption" => $post->caption,
                    "status" => $post->status,
                    "created_at" => $post->created_at,
                    "updated_at" => $post->updated_at,
                    "posts" => $attachments,
                ];
            }

            return response()->json($result);
        }
 
    public function create()
    {
        //
    }

 
    public function store(Request $request)
    {

    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        //
    }
 

    public function update(Request $request, string $id)
    {
        $followerCode = Auth::user()->code;

        if ($id === $followerCode) {
            return response()->json([
                'status' => false,
                'message' => 'You cannot follow yourself.'
            ], 400);
        }

        DB::beginTransaction();

        try {
            $followExists = DB::table('follows')
                ->where('follower_code', $followerCode)
                ->where('following_code', $id)
                ->exists();

            if ($followExists) {
                // Unfollow (delete record)
                DB::table('follows')
                    ->where('follower_code', $followerCode)
                    ->where('following_code', $id)
                    ->delete();

                $message = 'Unfollowed successfully.';
                $followStatus = 'none';
            } else {
                // Follow (insert record)
                DB::table('follows')->insert([
                    'follower_code' => $followerCode,
                    'following_code' => $id,
                    'follow_status' => 'pending',
                    'created_at' => now(),
                ]);

                $message = 'Follow request sent.';
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

            return response()->json([
                'status' => false,
                'message' => 'Operation failed.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function destroy(string $id)
    {
        //
    }
}
