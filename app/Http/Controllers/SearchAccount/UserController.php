<?php

namespace App\Http\Controllers\SearchAccount;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Resource;

class UserController extends Controller
{
    // public function searchUsers(Request $request)
    // {
    //     $search = trim($request->input('search', ''));

    //     $users = DB::table('users')
    //         ->leftJoin('userprofiles', 'userprofiles.code', '=', 'users.code')
    //         ->leftJoin('userskills', 'userskills.code', '=', 'users.code')
    //         ->select(
    //             'users.code', 
    //             'users.role_code',
    //             'users.status', 
    //             'users.fullname', 
    //             'users.is_online', 
    //             DB::raw('GROUP_CONCAT(DISTINCT userskills.skills ORDER BY userskills.skills SEPARATOR ", ") as skills'),
    //             DB::raw('MIN(userprofiles.photo_pic) as photo_pic') // ✅ Replaced ANY_VALUE with MIN
    //         )
    //         ->where('users.status', 'A')
    //         ->when($search, function ($query, $search) {
    //             return $query->where(function ($q) use ($search) {
    //                 $q->where('users.fullname', 'LIKE', "%$search%")
    //                   ->orWhere('userskills.skills', 'LIKE', "%$search%");
    //             });
    //         })
    //         ->groupBy('users.code','users.role_code','users.status', 'users.fullname', 'users.is_online') // ✅ Ensure all selected fields are grouped
    //         ->orderByRaw("
    //             CASE 
    //                 WHEN users.fullname = ? THEN 1 
    //                 WHEN users.fullname LIKE ? THEN 2
    //                 WHEN GROUP_CONCAT(userskills.skills ORDER BY userskills.skills SEPARATOR ', ') LIKE ? THEN 3
    //                 ELSE 4 
    //             END ASC", [$search, "$search%", "%$search%"])
    //         ->orderByRaw("LOWER(users.fullname) ASC")
    //         ->get();

    //     // Separate online and offline users
    //     $onlineUsers = $users->where('is_online', true)->values();
    //     $offlineUsers = $users->where('is_online', false)->values();

    //     return response()->json([
    //         'success' => true,
    //         'online' => $onlineUsers,
    //         'offline' => $offlineUsers
    //     ]);
    // }

    //  public function searchUsersBypublic(Request $request)
    // {
    //     $search = trim($request->input('search', ''));

    //     $users = DB::table('users')
    //         ->leftJoin('userprofiles', 'userprofiles.code', '=', 'users.code')
    //         ->leftJoin('userskills', 'userskills.code', '=', 'users.code')
    //         ->select(
    //             'users.code', 
    //             'users.role_code',
    //             'users.status', 
    //             'users.fullname', 
    //             'users.is_online', 
    //             DB::raw('GROUP_CONCAT(DISTINCT userskills.skills ORDER BY userskills.skills SEPARATOR ", ") as skills'),
    //             DB::raw('MIN(userprofiles.photo_pic) as photo_pic') // ✅ Replaced ANY_VALUE with MIN
    //         )
    //         ->where('users.status', 'A')
    //         ->when($search, function ($query, $search) {
    //             return $query->where(function ($q) use ($search) {
    //                 $q->where('users.fullname', 'LIKE', "%$search%")
    //                   ->orWhere('userskills.skills', 'LIKE', "%$search%");
    //             });
    //         })
    //         ->groupBy('users.code','users.role_code','users.status', 'users.fullname', 'users.is_online') // ✅ Ensure all selected fields are grouped
    //         ->orderByRaw("
    //             CASE 
    //                 WHEN users.fullname = ? THEN 1 
    //                 WHEN users.fullname LIKE ? THEN 2
    //                 WHEN GROUP_CONCAT(userskills.skills ORDER BY userskills.skills SEPARATOR ', ') LIKE ? THEN 3
    //                 ELSE 4 
    //             END ASC", [$search, "$search%", "%$search%"])
    //         ->orderByRaw("LOWER(users.fullname) ASC")
    //         ->get();

    //     // Separate online and offline users
    //     $onlineUsers = $users->where('is_online', true)->values();
    //     $offlineUsers = $users->where('is_online', false)->values();

    //     return response()->json([
    //         'success' => true,
    //         'online' => $onlineUsers,
    //         'offline' => $offlineUsers
    //     ]);
    // }

    public function searchUsers(Request $request)
    {
        $search  = trim($request->input('search', ''));
        $page    = max((int) $request->input('page', 1), 1);
        $perPage = max((int) $request->input('per_page', 10), 1);

        $users = DB::table('users')
            ->leftJoin('userprofiles', 'userprofiles.code', '=', 'users.code')
            ->leftJoin('userskills', 'userskills.code', '=', 'users.code')
            ->leftJoin('resources', 'resources.code', '=', 'users.code')
            ->select(
                'users.code',
                'users.role_code',
                'users.status',
                'users.fullname',
                'users.is_online',
                DB::raw('MIN(resources.profession) as profession'),
                DB::raw('GROUP_CONCAT(DISTINCT userskills.skills ORDER BY userskills.skills SEPARATOR ", ") as skills'),
                DB::raw('MIN(userprofiles.photo_pic) as photo_pic') // ✅ Replaced ANY_VALUE with MIN
            )
            ->where('users.status', 'A')
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('users.fullname', 'LIKE', "%$search%")
                        ->orWhere('userskills.skills', 'LIKE', "%$search%")
                        ->orWhere('resources.profession', 'LIKE', "%$search%");
                });
            })
            ->groupBy('users.code', 'users.role_code', 'users.status', 'users.fullname', 'users.is_online') // ✅ Ensure all selected fields are grouped
            ->orderByRaw("
                CASE
                    WHEN users.fullname = ? THEN 1
                    WHEN users.fullname LIKE ? THEN 2
                    WHEN GROUP_CONCAT(userskills.skills ORDER BY userskills.skills SEPARATOR ', ') LIKE ? THEN 3
                    ELSE 4
                END ASC", [$search, "$search%", "%$search%"])
            ->orderByRaw("LOWER(users.fullname) ASC")
            ->get();

        return $this->paginateOnlineOffline($users, $page, $perPage);
    }

    public function searchUsersBypublic(Request $request)
    {
        $search  = trim($request->input('search', ''));
        $page    = max((int) $request->input('page', 1), 1);
        $perPage = max((int) $request->input('per_page', 10), 1);

        $users = DB::table('users')
            ->leftJoin('userprofiles', 'userprofiles.code', '=', 'users.code')
            ->leftJoin('userskills', 'userskills.code', '=', 'users.code')
            ->leftJoin('resources', 'resources.code', '=', 'users.code')
            ->select(
                'users.code',
                'users.role_code',
                'users.status',
                'users.fullname',
                'users.is_online',
                DB::raw('MIN(resources.profession) as profession'),
                DB::raw('GROUP_CONCAT(DISTINCT userskills.skills ORDER BY userskills.skills SEPARATOR ", ") as skills'),
                DB::raw('MIN(userprofiles.photo_pic) as photo_pic') // ✅ Replaced ANY_VALUE with MIN
            )
            ->where('users.status', 'A')
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('users.fullname', 'LIKE', "%$search%")
                        ->orWhere('userskills.skills', 'LIKE', "%$search%")
                        ->orWhere('resources.profession', 'LIKE', "%$search%");
                });
            })
            ->groupBy('users.code', 'users.role_code', 'users.status', 'users.fullname', 'users.is_online') // ✅ Ensure all selected fields are grouped
            ->orderByRaw("
                CASE
                    WHEN users.fullname = ? THEN 1
                    WHEN users.fullname LIKE ? THEN 2
                    WHEN GROUP_CONCAT(userskills.skills ORDER BY userskills.skills SEPARATOR ', ') LIKE ? THEN 3
                    ELSE 4
                END ASC", [$search, "$search%", "%$search%"])
            ->orderByRaw("LOWER(users.fullname) ASC")
            ->get();

        return $this->paginateOnlineOffline($users, $page, $perPage);
    }
    /**
     * Splits the full (already-filtered/sorted) user collection into
     * online/offline groups, then slices each to the requested page.
     *
     * NOTE: this re-runs the full query on every page request (same as the
     * original code did once). Fine for typical staff/user directory sizes;
     * if the users table gets very large, paginate online/offline with two
     * separate DB queries (adding an is_online filter + LIMIT/OFFSET)
     * instead of slicing an in-memory collection.
     */
    private function paginateOnlineOffline($users, int $page, int $perPage)
    {
        $onlineUsers  = $users->where('is_online', true)->values();
        $offlineUsers = $users->where('is_online', false)->values();

        $onlineTotal  = $onlineUsers->count();
        $offlineTotal = $offlineUsers->count();

        $offset = ($page - 1) * $perPage;

        $onlinePage  = $onlineUsers->slice($offset, $perPage)->values();
        $offlinePage = $offlineUsers->slice($offset, $perPage)->values();

        return response()->json([
            'success' => true,
            'online'  => $onlinePage,
            'offline' => $offlinePage,
            'meta' => [
                'current_page'    => $page,
                'per_page'        => $perPage,
                'online_total'    => $onlineTotal,
                'offline_total'   => $offlineTotal,
                'online_has_more'  => ($offset + $perPage) < $onlineTotal,
                'offline_has_more' => ($offset + $perPage) < $offlineTotal,
            ],
        ]);
    }
}
