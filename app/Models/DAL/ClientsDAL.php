<?php

namespace App\DataAccess;

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
