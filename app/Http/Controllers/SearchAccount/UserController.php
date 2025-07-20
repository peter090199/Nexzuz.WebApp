<?php

namespace App\Http\Controllers\SearchAccount;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\DB;


class UserController extends Controller
{
    public function searchUsers(Request $request)
    {
        $search = trim($request->input('search', ''));
    
        $users = DB::table('users')
            ->leftJoin('userprofiles', 'userprofiles.code', '=', 'users.code')
            ->leftJoin('userskills', 'userskills.code', '=', 'users.code')
            ->select(
                'users.code', 
                'users.status', 
                'users.fullname', 
                'users.is_online', 
                DB::raw('GROUP_CONCAT(DISTINCT userskills.skills ORDER BY userskills.skills SEPARATOR ", ") as skills'),
                DB::raw('MIN(userprofiles.photo_pic) as photo_pic') // ✅ Replaced ANY_VALUE with MIN
            )
            ->where('users.status', 'A')
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('users.fullname', 'LIKE', "%$search%")
                      ->orWhere('userskills.skills', 'LIKE', "%$search%");
                });
            })
            ->groupBy('users.code', 'users.status', 'users.fullname', 'users.is_online') // ✅ Ensure all selected fields are grouped
            ->orderByRaw("
                CASE 
                    WHEN users.fullname = ? THEN 1 
                    WHEN users.fullname LIKE ? THEN 2
                    WHEN GROUP_CONCAT(userskills.skills ORDER BY userskills.skills SEPARATOR ', ') LIKE ? THEN 3
                    ELSE 4 
                END ASC", [$search, "$search%", "%$search%"])
            ->orderByRaw("LOWER(users.fullname) ASC")
            ->get();
    
        // Separate online and offline users
        $onlineUsers = $users->where('is_online', true)->values();
        $offlineUsers = $users->where('is_online', false)->values();
    
        return response()->json([
            'success' => true,
            'online' => $onlineUsers,
            'offline' => $offlineUsers
        ]);
    }

    //searchhistory
  
    



}
