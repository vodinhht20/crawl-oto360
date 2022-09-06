<?php

namespace App\Repositories;

use App\Models\Product;

class ProductRepository extends BaseRepository {

    public function getModel()
    {
        return Product::class;
    }
}