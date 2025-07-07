<?php

namespace App\Http\Controllers\Follow;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use App\Models\ClientsDAL; // DAL 

class ClientsBAL extends Controller
{
    protected $resourceDAL;

    public function __construct(ClientsDAL $resourceDAL)
    {
        $this->resourceDAL = $resourceDAL;
    }

    public function getListClients2()
    {
       return $this->resourceDAL->getListClients2();
    }

}
