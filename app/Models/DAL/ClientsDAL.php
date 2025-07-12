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


    public function getListClients()
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

 
    public function getFollowStatus(string $code)
    {
        $currentUserCode = Auth::user()->code;

        if ($code === $currentUserCode) {
            return response()->json(['follow_status' => null]);
        }

        $record = DB::selectOne('
            SELECT follow_status 
            FROM follows 
            WHERE follower_code = ? AND following_code = ? 
            LIMIT 1
        ', [$currentUserCode, $code]);

        return response()->json([
            'follow_status' => $record->follow_status ?? 'none'
        ]);
    }



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
                SET follow_status = "accepted", updated_at = NOW()
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
