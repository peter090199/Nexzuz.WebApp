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

class FollowController extends Controller
{
    // public function getPostxx()
    // {
    //     $currentUserCode = Auth::user()->code;

    //     // 1️⃣ Fetch posts related to user or accepted follows
    //     $posts = DB::table('posts as p')
    //         ->leftJoin('follows as f1', function ($join) use ($currentUserCode) {
    //             $join->on('f1.following_code', '=', 'p.code')
    //                 ->where('f1.follower_code', '=', $currentUserCode)
    //                 ->where('f1.follow_status', '=', 'accepted');
    //         })
    //         ->leftJoin('follows as f2', function ($join) use ($currentUserCode) {
    //             $join->on('f2.follower_code', '=', 'p.code')
    //                 ->where('f2.following_code', '=', $currentUserCode)
    //                 ->where('f2.follow_status', '=', 'accepted');
    //         })
    //         ->where('p.status', 1)
    //         ->where(function ($query) use ($currentUserCode) {
    //             $query->whereNotNull('f1.follower_code')
    //                 ->orWhereNotNull('f2.following_code')
    //                 ->orWhere('p.code', '=', $currentUserCode);
    //         })
    //         ->orderByDesc('p.created_at')
    //         ->select('p.id', 'p.posts_uuid', 'p.caption', 'p.status', 'p.created_at', 'p.updated_at', 'p.code as post_owner')
    //         ->limit(50) // optional for faster load
    //         ->get();

    //     if ($posts->isEmpty()) {
    //         return response()->json(['success' => true, 'data' => []]);
    //     }

    //     $userCodes = $posts->pluck('post_owner')->unique();
    //     $postUuids = $posts->pluck('posts_uuid');

    //     // 2️⃣ Fetch user info
    //     $users = DB::table('users')
    //         ->whereIn('code', $userCodes)
    //         ->select([
    //             'code',
    //             DB::raw("IFNULL(full_name, 'Unknown User') AS fullname"), // adjust column if needed
    //             DB::raw("IFNULL(photo, '') AS profile_pic") // adjust column if needed
    //         ])
    //         ->get()
    //         ->keyBy('code');

    //     // 3️⃣ Fetch attachments
    //     $attachments = DB::table('attachmentposts')
    //         ->whereIn('posts_uuid', $postUuids)
    //         ->where('status', 1)
    //         ->select('posts_uuid', 'posts_type', 'file_url') // replace file_url if different
    //         ->get()
    //         ->groupBy('posts_uuid');

    //     // 4️⃣ Map posts to include user info and attachments
    //     $result = $posts->map(function ($post) use ($users, $attachments) {
    //         $user = $users[$post->post_owner] ?? null;
    //         $files = $attachments[$post->posts_uuid] ?? collect();

    //         return [
    //             'id'          => $post->id,
    //             'posts_uuid'  => $post->posts_uuid,
    //             'caption'     => $post->caption,
    //             'status'      => $post->status,
    //             'created_at'  => $post->created_at,
    //             'updated_at'  => $post->updated_at,
    //             'fullname'    => $user->fullname ?? 'Unknown User',
    //             'profile_pic' => $user->profile_pic ?? '',
    //             'images'      => $files->where('posts_type', 'image')->pluck('file_url')->values(),
    //             'videos'      => $files->where('posts_type', 'video')->pluck('file_url')->values(),
    //         ];
    //     });

    //     return response()->json([
    //         'success' => true,
    //         'data' => $result,
    //     ]);
    // }


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
                ->where(function($q) use ($currentUserCode) {
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
    // public function getFollowedPosts(Request $request)
    // {
    //     $currentUserCode = Auth::user()->code;
    //     $page = $request->query('page', 1);
    //     $perPage = $request->query('per_page', 5);
    //     $offset = ($page - 1) * $perPage;

    //     $data = DB::select(
    //         '
    //         SELECT 
    //             (SELECT getUserprofilepic(p.code)) AS profile_pic,
    //             (SELECT getFullname(p.code)) AS fullname,
    //             p.id,
    //             p.posts_uuid,
    //             p.caption,
    //             p.status,
    //             p.created_at,
    //             p.updated_at,
    //             p.code AS post_owner
    //         FROM posts AS p
    //         LEFT JOIN follows AS f1 
    //             ON f1.following_code = p.code AND f1.follower_code = ? AND f1.follow_status = "accepted"
    //         LEFT JOIN follows AS f2 
    //             ON f2.follower_code = p.code AND f2.following_code = ? AND f2.follow_status = "accepted"
    //         WHERE p.status = 1
    //         AND (
    //             f1.follower_code IS NOT NULL
    //             OR f2.following_code IS NOT NULL
    //             OR p.code = ?
    //         )
    //         ORDER BY p.created_at DESC
    //         LIMIT ? OFFSET ?
    //         ',
    //         [$currentUserCode, $currentUserCode, $currentUserCode, $perPage, $offset]
    //     );

    //     $result = [];

    //     foreach ($data as $post) {
    //         // Get attachments for post
    //         $attachments = DB::table('attachmentposts')
    //             ->where('posts_uuid', $post->posts_uuid)
    //             ->where(function($query) use ($currentUserCode) {
    //                 $query->where('status', 1)
    //                       ->orWhere('code', $currentUserCode);
    //             })
    //             ->get();

    //         $images = $attachments->where('posts_type', 'image')->values();
    //         $videos = $attachments->where('posts_type', 'video')->values();

    //         $result[] = [
    //             'id' => $post->id,
    //             'profile_pic' => $post->profile_pic,
    //             'fullname' => $post->fullname,
    //             'posts_uuid' => $post->posts_uuid,
    //             'caption' => $post->caption,
    //             'status' => $post->status,
    //             'created_at' => $post->created_at,
    //             'updated_at' => $post->updated_at,
    //             'images' => $images,
    //             'videos' => $videos,
    //         ];
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'data' => $result
    //     ]);
    // }

    // public function getPost()
    // {
    //     $currentUserCode = Auth::user()->code;

    //     $data = DB::select('
    //         SELECT 
    //             (SELECT getUserprofilepic(p.code)) AS profile_pic,
    //             (SELECT getFullname(p.code)) AS fullname,
    //             p.id,
    //             p.posts_uuid,
    //             p.caption,
    //             p.status,
    //             p.created_at,
    //             p.updated_at,
    //             p.code AS post_owner
    //         FROM posts AS p
    //         LEFT JOIN follows AS f1 
    //             ON f1.following_code = p.code AND f1.follower_code = ? AND f1.follow_status = "accepted"
    //         LEFT JOIN follows AS f2 
    //             ON f2.follower_code = p.code AND f2.following_code = ? AND f2.follow_status = "accepted"
    //         WHERE p.status = 1
    //         AND (
    //             f1.follower_code IS NOT NULL
    //             OR f2.following_code IS NOT NULL
    //             OR p.code = ?
    //         )
    //         ORDER BY p.created_at DESC
    //     ', [$currentUserCode, $currentUserCode, $currentUserCode]);

    //     $result = [];

    //     foreach ($data as $post) {
    //         // Fetch attachments grouped by type
    //         $attachments = DB::table('attachmentposts')
    //             ->where('posts_uuid', $post->posts_uuid)
    //             ->where(function($query) use ($currentUserCode, $post) {
    //                 $query->where('status', 1)
    //                     ->orWhere('code', $currentUserCode);
    //             })
    //             ->get();

    //         $images = $attachments->where('posts_type', 'image')->values();
    //         $videos = $attachments->where('posts_type', 'video')->values();

    //         $result[] = [
    //             "id"=> $post->id,
    //             "profile_pic" => $post->profile_pic,
    //             "fullname" => $post->fullname,
    //             "posts_uuid" => $post->posts_uuid,
    //             "caption" => $post->caption,
    //             "status" => $post->status,
    //             "created_at" => $post->created_at,
    //             "updated_at" => $post->updated_at,
    //             "images" => $images,
    //             "videos" => $videos
    //         ];
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'data' => $result
    //     ]);
    // }

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
                    "video"
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
