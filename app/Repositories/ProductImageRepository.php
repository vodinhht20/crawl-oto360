<?php

namespace App\Repositories;

use App\Models\ProductImage;

class ProductImageRepository extends BaseRepository {

    public function getModel()
    {
        return ProductImage::class;
    }
}