<?php

namespace App\Http\Controllers\Follow;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use App\Models\Resource; // DAL 

class Clients extends Controller
{
  
    public function getListClients2()
    {
        $data = DB::table('resources')
            ->select('fullname', 'profession', 'role_code')
            ->get();

        return response()->json($data);
    }


}
