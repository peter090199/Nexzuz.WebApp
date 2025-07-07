<?php

namespace App\Http\Controllers\Follow;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use App\DataAccess\DAL\ClientsDAL;
class ClientsBAL extends Controller
{
    protected $resourceDAL;

    public function __construct(ClientsDAL $resourceDAL)
    {
        $this->resourceDAL = $resourceDAL;
    }

    public function getListClients()
    {
       return $this->resourceDAL->getListClients();
    }

}
