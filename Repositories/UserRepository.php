<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
class UserRepository
{
    public function create(array $data)
    {
        $data['role'] =$data['role'];
        return User::create($data);
    }

    // You can add other methods here as needed
}
