<?php

namespace App\Business;

use App\DataAccess\ClientsDAL;

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
