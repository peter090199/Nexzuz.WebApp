<?php

namespace App\Models\DAL;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

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
            LIMIT 1
        ', [$currentUserCode, $code, $code, $currentUserCode]);

        return response()->json([
            'follow_status' => $record->follow_status ?? 'none'
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

}
