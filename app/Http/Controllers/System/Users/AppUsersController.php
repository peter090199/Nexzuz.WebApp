<?php

namespace App\Http\Controllers\System\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; 

class AppUsersController extends Controller
{
   public function getUsers()
    {
        $users = DB::table('users')->get();
        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }
    


}
