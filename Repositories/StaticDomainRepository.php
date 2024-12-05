<?php

namespace App\Repositories;

use App\Models\StaticDnsModel;

class StaticDomainRepository
{
    public function create(array $data)
    {
        return StaticDnsModel::create($data);
    }

    public function getDns(){
        return StaticDnsModel::first()->toArray();;
    }
}
