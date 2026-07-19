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
use Illuminate\Support\Facades\DB;
use App\Helpers\SharedRoutine;

class FollowController extends Controller
{

    public function reactToPostById(Request $request)
    {
        $userCode = Auth::user()->code; // authenticated user
        $postId = $request->post_id;
        $reactionType = $request->reaction;

        // Check if post_id is provided
        if (!$postId) {
            return response()->json([
                'success' => false,
                'message' => 'Post ID is required'
            ], 400);
        }

        // Check if the post exists in the database
        $postExists = DB::table('posts')->where('id', $postId)->exists();

        if (!$postExists) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found in the database'
            ], 404);
        }

        // Validate reaction
        $request->validate([
            'reaction' => 'required|string|max:50',
        ]);

        // Insert or update reaction
        DB::table('reactionPost')->updateOrInsert(
            [
                'post_id' => $postId,
                'code'    => $userCode,
            ],
            [
                'reaction'   => $reactionType,
                'updated_at' => now(),
                'create_at'  => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Reaction saved successfully',
        ]);
    }

    // Get current user's reaction for a post
    public function getReactionByPostId($id)
    {
        $userCode = Auth::user()->code;

        // Check if the post exists
        $postExists = DB::table('posts')->where('id', $id)->exists();
        if (!$postExists) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found'
            ], 404);
        }

        // Get the user's reaction for the post
        $reaction = DB::table('reactionPost')
            ->where('post_id', $id)
            ->where('code', $userCode)
            ->value('reaction'); // returns null if no reaction

        return response()->json([
            'success'  => true,
            'reaction' => $reaction
        ]);
    }

    public function getFollowedPosts(Request $request)
    {
        $currentUserCode = Auth::user()->code;
        $page = $request->query('page', 1);
        $perPage = $request->query('per_page', 5);
        $since = $request->query('since'); // new posts timestamp
        $offset = ($page - 1) * $perPage;

        // Build base query
        $query = '
            SELECT 
                (SELECT getUserprofilepic(p.code)) AS profile_pic,
                (SELECT getFullname(p.code)) AS fullname,
                p.id,
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
        ';

        $bindings = [$currentUserCode, $currentUserCode, $currentUserCode];

        // If fetching new posts only
        if ($since) {
            $query .= ' AND p.created_at > ?';
            $bindings[] = $since;
        }

        $query .= ' ORDER BY p.created_at DESC';

        // Apply pagination only if not fetching new posts
        if (!$since) {
            $query .= ' LIMIT ? OFFSET ?';
            $bindings[] = $perPage;
            $bindings[] = $offset;
        }

        $data = DB::select($query, $bindings);

        $result = [];

        foreach ($data as $post) {
            $attachments = DB::table('attachmentposts')
                ->where('posts_uuid', $post->posts_uuid)
                ->where(function ($q) use ($currentUserCode) {
                    $q->where('status', 1)
                        ->orWhere('code', $currentUserCode);
                })
                ->get();

            $images = $attachments->where('posts_type', 'image')->values();
            $videos = $attachments->where('posts_type', 'video')->values();

            $result[] = [
                'id' => $post->id,
                'profile_pic' => $post->profile_pic,
                'fullname' => $post->fullname,
                'posts_uuid' => $post->posts_uuid,
                'caption' => $post->caption,
                'status' => $post->status,
                'created_at' => $post->created_at,
                'updated_at' => $post->updated_at,
                'images' => $images,
                'videos' => $videos,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }


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
                "video"
            ];
        }

        return response()->json($result);
    }


    public function getPost()
    {
        $currentUserCode = Auth::user()->code;

        $data = DB::select("
                SELECT 
                    pf.photo_pic AS profile_pic,
                    CONCAT(u.fname, ' ', u.lname) AS fullname,
                    u.role_code,
                    u.is_online,
                    p.id,
                    p.code,
                    p.transNo,
                    p.posts_uuid,
                    p.caption,
                    p.status,
                    p.created_at,
                    p.updated_at,
                    p.code AS post_owner
                FROM posts AS p
                LEFT JOIN users AS u ON u.code = p.code
                LEFT JOIN userprofiles AS pf ON pf.code = u.code
                LEFT JOIN follows AS f1 
                    ON f1.following_code = p.code 
                    AND f1.follower_code = ? 
                    AND f1.follow_status = 'accepted'
                LEFT JOIN follows AS f2 
                    ON f2.follower_code = p.code 
                    AND f2.following_code = ? 
                    AND f2.follow_status = 'accepted'
                WHERE p.status = 1
                AND (
                    f1.follower_code IS NOT NULL
                    OR f2.following_code IS NOT NULL
                    OR p.code = ?
                    OR u.role_code = 'DEF-MASTERADMIN' 
                )
                ORDER BY p.created_at DESC
            ", [$currentUserCode, $currentUserCode, $currentUserCode]);

        $result = [];

        foreach ($data as $post) {

            $attachments = DB::table('attachmentposts')
                ->where('posts_uuid', $post->posts_uuid)
                ->where(function ($query) use ($currentUserCode) {
                    $query->where('status', 1)
                        ->orWhere('code', $currentUserCode);
                })
                ->get();

            $images = $attachments->where('posts_type', 'image')->values();
            $videos = $attachments->where('posts_type', 'video')->values();

            $result[] = [
                "id" => $post->id,
                "transNo" => $post->transNo,
                "profile_pic" => $post->profile_pic,
                "fullname" => $post->fullname,
                "is_online" => $post->is_online,
                "role_code" => $post->role_code,
                "code" => $post->code,
                "posts_uuid" => $post->posts_uuid,
                "caption" => $post->caption,
                "status" => $post->status,
                "created_at" => $post->created_at,
                "updated_at" => $post->updated_at,
                "images" => $images,
                "videos" => $videos
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }

    public function create()
    {
        //
    }

    //follow or unfollow a user
    public function update(string $code)
    {
        $user = Auth::user();
        $followerCode = $user->code;

        // Prevent following yourself
        if ($code === $followerCode) {
            return response()->json([
                'status' => false,
                'message' => 'You cannot follow yourself.'
            ], 400);
        }

        DB::beginTransaction();

        try {

            $follow = DB::table('follows')
                ->where('follower_code', $followerCode)
                ->where('following_code', $code)
                ->first();

            /**
             * Existing follow record
             */
            if ($follow) {

                DB::table('follows')
                    ->where('id', $follow->id)
                    ->delete();

                DB::commit();

                return response()->json([
                    'status' => true,
                    'message' => $follow->follow_status === 'pending'
                        ? 'Follow request cancelled.'
                        : 'Unfollowed successfully.',
                    'follow_status' => 'none'
                ]);
            }

            /**
             * Check connection limit
             */
            $connection = SharedRoutine::canAddConnection($followerCode);
            if (!$connection['status']) {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => $connection['message'],
                    'current_connections' => $connection['current'],
                    'connection_limit' => $connection['limit']
                ], 422);
            }

            /**
             * Create follow request
             */
            DB::table('follows')->insert([
                'follower_code' => $followerCode,
                'following_code' => $code,
                'follow_status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Follow request sent.',
                'follow_status' => 'pending',
                'current_connections' => $connection['current'] + 1,
                'connection_limit' => $connection['limit']
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

    // public function update(Request $request, string $id)
    // {
    //     $user = Auth::user();
    //     $followerCode = $user->code;

    //     // Cannot follow yourself
    //     if ($id == $followerCode) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'You cannot follow yourself.'
    //         ], 400);
    //     }

    //     DB::beginTransaction();

    //     try {

    //         // Check if already following
    //         $followExists = DB::table('follows')
    //             ->where('follower_code', $followerCode)
    //             ->where('following_code', $id)
    //             ->exists();

    //         if ($followExists) {

    //             // UNFOLLOW
    //             DB::table('follows')
    //                 ->where('follower_code', $followerCode)
    //                 ->where('following_code', $id)
    //                 ->delete();

    //             DB::commit();

    //             return response()->json([
    //                 'status' => true,
    //                 'message' => 'Unfollowed successfully.',
    //                 'follow_status' => 'not_following'
    //             ]);
    //         }

    //         // CHECK CONNECTION LIMIT
    //       $connection = SharedRoutine::canAddConnection($followerCode);
    //         if (!$connection['status']) {

    //             DB::rollBack();

    //             return response()->json([
    //                 'status' => false,
    //                 'message' => $connection['message'],
    //                 'current_connections' => $connection['current'],
    //                 'connection_limit' => $connection['limit']
    //             ], 204);
    //         }

    //         // FOLLOW
    //         DB::table('follows')->insert([
    //             'follower_code' => $followerCode,
    //             'following_code' => $id,
    //             'follow_status' => 'pending',
    //             'created_at' => now(),
    //             'updated_at' => now(),
    //         ]);

    //         DB::commit();

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Follow request sent.',
    //             'follow_status' => 'pending',
    //             'current_connections' => $connection['current'] + 1,
    //             'connection_limit' => $connection['limit']
    //         ]);
    //     } catch (\Exception $e) {

    //         DB::rollBack();

    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Operation failed.',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function updatexx(Request $request, string $id)
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
                $followStatus = 'not_following';
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
