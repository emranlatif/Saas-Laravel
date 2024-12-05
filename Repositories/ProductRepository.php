<?php

namespace App\Repositories;

use App\Models\Products;

class ProductRepository
{
    public function create(array $data)
    {
        return Products::create($data);
    }

    // You can add other methods here as needed
}
