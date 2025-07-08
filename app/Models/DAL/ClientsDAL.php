<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class ClientsDAL
{
    public function getListClients()
    {
        return DB::table('resources')
            ->select('fullname', 'profession', 'role_code')
            ->get();
    }
}
