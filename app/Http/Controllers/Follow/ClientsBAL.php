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
   
    public function getfollowingPending()
    {
        return $this->clientsDAL->getfollowingPending();
    }
    public function getPendingFollowStatus($code)
    {
        return $this->clientsDAL->getPendingFollowStatus($code);
    }
    public function unfollow($id)
    {
        return $this->clientsDAL->unfollow($id);
    }
    
    public function getPeopleyoumayknow()
    {
        return $this->clientsDAL->getPeopleyoumayknow();
    }
        public function getPeopleRecentActivity()
    {
        return $this->clientsDAL->getPeopleRecentActivity();
    }

    

}
