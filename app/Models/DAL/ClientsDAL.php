<?php

namespace App\Models\DAL;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ClientsDAL extends Model
{
    use HasApiTokens, HasFactory, Notifiable;
    
    protected $fillable = [
        'code',
        'fname',
        'lname',
        'mname',
        'fullname',
        'contact_no',
        'age',
        'email',
        'profession',
        'company',
        'industry',
        'companywebsite',
        'role_code',
        'designation',
        'h1_fname',
        'h1_lname',
        'h1_mname',
        'h1_fullname',
        'h1_contact_no',
        'h1_email',
        'h1_address1',
        'h1_address2',
        'h1_city',
        'h1_province',
        'h1_postal_code',
        'h1_companycode',
        'h1_rolecode',
        'h1_designation',
        'created_by',
        'updated_by'
    ];

    //get list user and clients
    public function getListClients()
    {
        $currentUserCode = Auth::user()->code;

        $clients = DB::table('follows')
            ->leftJoin('users', function ($join) {
                $join->on('users.code', '=', 'follows.follower_code')
                    ->orOn('users.code', '=', 'follows.following_code');
            })
            ->leftJoin('resources', 'resources.code', '=', 'users.code')
            ->leftJoin('userprofiles', 'userprofiles.code', '=', 'users.code')
            ->select(
                'userprofiles.photo_pic',
                'resources.fullname',
                'resources.profession',
                'resources.company',
                'resources.industry',
                'follows.follower_code',
                'follows.following_code',
                'follows.follow_status',
                'users.code',
                'users.is_online'
            )
            ->where('users.status', 'A')
            ->where('users.code', '!=', $currentUserCode)
            ->where(function ($query) use ($currentUserCode) {
                $query->where('follows.follower_code', $currentUserCode)
                    ->orWhere('follows.following_code', $currentUserCode);
            })
            ->distinct()
            ->get();

        return [
            'count' => $clients->count(),
            'data' => $clients
        ];
    }

        public function getListClientsxxc()
        {
            $currentUserCode = Auth::user()->code;

            $clients = DB::table('resources')
                ->leftJoin('userprofiles', 'resources.code', '=', 'userprofiles.code')
                ->leftJoin('users', 'resources.code', '=', 'users.code')
                ->select(
                    'userprofiles.photo_pic',
                    'resources.fullname',
                    'resources.profession',
                    'resources.company',
                    'resources.industry',
                    'users.code',
                    'users.is_online'
                )
                ->where('users.status', 'A')
                ->where('users.code', '!=', $currentUserCode) // Exclude current user
                ->get();

            return [
                'count' => $clients->count(),
                'data' => $clients
            ];
        }

    public function getListClientsxx()
    {
        $clients = DB::table('resources')
            ->leftJoin('userprofiles', 'resources.code', '=', 'userprofiles.code')
            ->leftJoin('users', 'resources.code', '=', 'users.code')
            ->select(
                'userprofiles.photo_pic',
                'resources.fullname',
                'resources.profession',
                'resources.company',
                'resources.industry',
                'users.code',
                'users.is_online'
            )
            ->where('users.status', 'A')
            ->get();

        return [
            'count' => $clients->count(),
            'data' => $clients
        ];
    }

    public function getPendingFollowStatus(string $code)
    {
        $currentUserCode = Auth::user()->code;

        // Prevent checking follow status with yourself
        if ($code === $currentUserCode) {
            return response()->json([
                'follow_status' => null,
                'data' => []
            ]);
        }

        // Get all follow records between the two users (bi-directional)
        $records = DB::select('
            SELECT * 
            FROM follows 
            WHERE (
                (follower_code = ? AND following_code = ?)
                OR 
                (follower_code = ? AND following_code = ?)
            )
        ', [$currentUserCode, $code, $code, $currentUserCode]);

        // Determine follow_status from the first record if available
        $status = count($records) > 0 ? $records[0]->follow_status : 'none';

        // Return full data and status
        return response()->json([
            'follow_status' => $status,
            'data' => $records
        ]);
    }

   public function getPendingFollowStatusxx(string $code)
    {
        $currentUserCode = Auth::user()->code;

        if ($code === $currentUserCode) {
            return response()->json(['follow_status' => null]);
        }

        $record = DB::selectOne('
            SELECT  * 
            FROM follows 
            WHERE (
                (follower_code = ? AND following_code = ?)
                OR 
                (follower_code = ? AND following_code = ?)
            )
            LIMIT 1
        ', [$currentUserCode, $code, $code, $currentUserCode]);

        return response()->json([
            'follow_status' => $record->follow_status ?? 'none',

        ]);
    }

    //get follow status
    public function getFollowStatus(string $code)
    {
        $currentUserCode = Auth::user()->code;

        if ($code === $currentUserCode) {
            return response()->json(['follow_status' => null]);
        }

        $record = DB::selectOne('
            SELECT follow_status 
            FROM follows 
            WHERE (
                (follower_code = ? AND following_code = ?)
                OR 
                (follower_code = ? AND following_code = ?)
            )
            AND follow_status = "accepted"
            LIMIT 1
        ', [$currentUserCode, $code, $code, $currentUserCode]);

        return response()->json([
            'follow_status' => $record->follow_status ?? 'none'
        ]);
    }




    //get follower pending
    public function getPendingFollowRequests()
    {
        $currentUserCode = Auth::user()->code;

        $pendingFollows = DB::table('follows')
            ->join('users', 'users.code', '=', 'follows.follower_code') // user who sent the request
            ->join('resources', 'resources.code', '=', 'users.code')
            ->leftJoin('userprofiles', 'userprofiles.code', '=', 'users.code')
            ->select(
                'userprofiles.photo_pic',
                'resources.fullname',
                'resources.profession',
                'resources.company',
                'resources.industry',
                'users.code as user_code',
                'users.is_online',
                'follows.follower_code',
                'follows.follow_status'
            )
            ->where('follows.following_code', $currentUserCode) // <- YOU are the one being followed
            ->where('follows.follow_status', 'pending')
            ->where('users.status', 'A')
            ->get();

        return response()->json([
            'count' => $pendingFollows->count(),
            'data' => $pendingFollows
        ]);
    }


    //get following pending
    public function getfollowingPending()
    {
        $currentUserCode = Auth::user()->code;

        $pendingFollows = DB::table('resources')
            ->leftJoin('userprofiles', 'resources.code', '=', 'userprofiles.code')
            ->leftJoin('users', 'resources.code', '=', 'users.code')
            ->leftJoin('follows', function ($join) use ($currentUserCode) {
                $join->on('follows.following_code', '=', 'users.code')
                    ->where('follows.follower_code', '=', $currentUserCode);
            })
            ->select(
                'userprofiles.photo_pic',
                'resources.fullname',
                'resources.profession',
                'resources.company',
                'resources.industry',
                'users.code',
                'users.is_online',
                'follows.follow_status',
                'follows.follower_code',
                'follows.following_code'
            )
            ->where('follows.follow_status', '=', 'pending')
            ->get();

        return response()->json([
            'status' => true,
            'count' => $pendingFollows->count(),
            'data' => $pendingFollows
        ]);
    }

    //accepted follow 
    public function acceptFollowRequest(string $followerCode)
    {
        $currentUserCode = Auth::user()->code;

        try {
            // Check if a pending request exists
            $follow = DB::selectOne('
                SELECT * FROM follows 
                WHERE follower_code = ? AND following_code = ? AND follow_status = "pending"
            ', [$followerCode, $currentUserCode]);

            if (!$follow) {
                return response()->json(['status' => false, 'message' => 'No pending request found'], 404);
            }

            // Update follow status to 'accepted'
            DB::update('
                UPDATE follows 
                SET follow_status = "accepted", created_at = NOW()
                WHERE follower_code = ? AND following_code = ?
            ', [$followerCode, $currentUserCode]);

            return response()->json([
                'status' => true,
                'message' => 'Follow request accepted',
                'follow_status' => 'accepted',
                'follower_code' => $followerCode,
                'following_code' => $currentUserCode
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Operation failed', 'error' => $e->getMessage()], 500);
        }
    }


    //unfollow
    public function unfollow($id)
    {
        $currentUserCode = Auth::user()->code;

        // Optional: Check if the record belongs to the authenticated user
        $follow = DB::table('follows')
            ->where('id', $id)
            ->where(function ($query) use ($currentUserCode) {
                $query->where('follower_code', $currentUserCode)
                    ->orWhere('following_code', $currentUserCode);
            })
            ->first();

        if (!$follow) {
            return response()->json([
                'status' => false,
                'message' => 'Follow record not found or access denied.'
            ], 404);
        }

        // Delete the record
        DB::table('follows')->where('id', $id)->delete();

        return response()->json([
            'status' => true,
            'message' => 'Unfollowed successfully.',
            'follow_status' => 'none'
        ]);
    }


     // Suggested users based on profession or industry of followed people
    public function getPeopleyoumayknow(): JsonResponse
    {
        try {
            $code = Auth::user()->code;

            // Get user's profession
            $profession = DB::table('resources')->where('code', $code)->value('profession');

            $results = DB::select("
                SELECT 
                    s.photo_pic,
                    s.fullname,
                    s.profession,
                    s.company,
                    s.industry,
                    s.code,
                    s.is_online,
                    s.source,
                    f.id,
                    COALESCE(f.follow_status, 'none') AS follow_status
                FROM (
                    -- Suggested users only (no UNION, no history)
                    SELECT 
                        up.photo_pic,
                        r.fullname,
                        r.profession,
                        r.company,
                        r.industry,
                        u.code,
                        u.is_online,
                        'suggested' AS source
                    FROM users u
                    INNER JOIN resources r ON u.code = r.code
                    LEFT JOIN userprofiles up ON u.code = up.code
                    WHERE u.status = 'A'
                    AND u.code != ?
                    AND (
                        r.profession = ?
                        OR EXISTS (
                            SELECT 1
                            FROM follows f
                            JOIN resources r2 ON f.following_code = r2.code
                            WHERE f.follower_code = ?
                            AND r2.industry = r.industry
                        )
                    )
                ) s
                LEFT JOIN follows f 
                    ON f.follower_code = ?
                    AND f.following_code = s.code
                ORDER BY s.fullname ASC
            ", [$code, $profession, $code, $code]);

            return response()->json([
                'success' => true,
                'count' => count($results),
                'data' => $results,
            ]);

        } catch (\Exception $e) {
            \Log::error('People you may know error', [
                'user_code' => Auth::user()->code,
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again later.'
            ], 500);
        }
    }

    public function getPeopleRecentActivity(): JsonResponse
    {
        try {
            $code = Auth::user()->code;

            $results = DB::select("
                SELECT 
                    up.photo_pic,
                    r.fullname,
                    r.profession,
                    r.company,
                    r.industry,
                    u.code,
                    u.is_online,
                    'history' AS source,
                    f.id,
                    COALESCE(f.follow_status, 'none') AS follow_status
                FROM users u
                INNER JOIN resources r ON u.code = r.code
                LEFT JOIN userprofiles up ON u.code = up.code
                LEFT JOIN follows f ON f.follower_code = ? AND f.following_code = u.code
                WHERE u.status = 'A'
                    AND u.code != ?
                    AND EXISTS (
                        SELECT 1 
                        FROM user_activity ua
                        WHERE ua.viewer_code = ?
                            AND ua.viewed_code = u.code
                    )
                ORDER BY r.fullname ASC
            ", [$code, $code, $code]);

            return response()->json([
                'success' => true,
                'count' => count($results),
                'data' => $results,
            ]);

        } catch (\Exception $e) {
            \Log::error('People you may know error', [
                'user_code' => Auth::user()->code,
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again later.'
            ], 500);
        }
    }


     public function getPeopleyoumayknowxxx(): JsonResponse
        {
          try {
                $code = Auth::user()->code;

                // Get user's profession
                $profession = DB::table('resources')->where('code', $code)->value('profession');

                $results = DB::select("
                    SELECT 
                        s.photo_pic,
                        s.fullname,
                        s.profession,
                        s.company,
                        s.industry,
                        s.code,
                        s.is_online,
                        s.source,
                        f.id,
                        COALESCE(f.follow_status, 'none') AS follow_status
                    FROM (
                        SELECT 
                            up.photo_pic,
                            r.fullname,
                            r.profession,
                            r.company,
                            r.industry,
                            u.code,
                            u.is_online,
                            'suggested' AS source
                        FROM users u
                        INNER JOIN resources r ON u.code = r.code
                        LEFT JOIN userprofiles up ON u.code = up.code
                        WHERE u.status = 'A'
                        AND u.code != ?
                        AND (
                            r.profession = ?
                            OR EXISTS (
                                SELECT 1
                                FROM follows f
                                JOIN resources r2 ON f.following_code = r2.code
                                WHERE f.follower_code = ?
                                    AND r2.industry = r.industry
                            )
                        )

                        UNION

                        -- History (viewed before)
                        SELECT 
                            up.photo_pic,
                            r.fullname,
                            r.profession,
                            r.company,
                            r.industry,
                            u.code,
                            u.is_online,
                            'history' AS source
                        FROM users u
                        INNER JOIN resources r ON u.code = r.code
                        LEFT JOIN userprofiles up ON u.code = up.code
                        WHERE u.status = 'A'
                        AND u.code != ?
                        AND EXISTS (
                            SELECT 1 
                            FROM user_activity ua
                            WHERE ua.viewer_code = ?
                                AND ua.viewed_code = u.code
                        )
                    ) s
                    LEFT JOIN follows f 
                        ON f.follower_code = ?
                    AND f.following_code = s.code
                    ORDER BY s.fullname ASC
                ", [$code, $profession, $code, $code, $code, $code]);

                return response()->json([
                    'success' => true,
                    'count' => count($results),
                    'data' => $results,
                ]);

            } catch (\Exception $e) {
                \Log::error('People you may know error', [
                    'user_code' => Auth::user()->code,
                    'message' => $e->getMessage()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Something went wrong. Please try again later.'
                ], 500);
            }
        }




}
