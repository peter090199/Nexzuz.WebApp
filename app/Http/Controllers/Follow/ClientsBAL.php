<?php

namespace App\Http\Controllers\Follow;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DAL\ClientsDAL;
use Illuminate\Support\Facades\Auth;

class ClientsBAL extends Controller
{
    protected $clientsDAL;

    public function __construct(ClientsDAL $clientsDAL)
    {
        $this->clientsDAL = $clientsDAL;
    }

    public function getListClients()
    {
        return $this->clientsDAL->getListClients();
    }

    public function getFollowStatus($code)
    {
        return $this->clientsDAL->getFollowStatus($code);
    }
    
    public function getPendingFollowRequests()
    {
        return $this->clientsDAL->getPendingFollowRequests();
    }

     public function acceptFollowRequest($code)
    {
        return $this->clientsDAL->acceptFollowRequest($code);
    }

    

}
