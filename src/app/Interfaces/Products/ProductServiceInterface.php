<?php

namespace App\Interfaces\Products;

use App\DTOs\Products\ProductData;
use App\DTOs\Products\ProductFilterData;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductServiceInterface
{
    public function list(ProductFilterData $filters): LengthAwarePaginator;

    public function find(int $id): Product;

    public function create(ProductData $data): Product;

    public function update(int $id, ProductData $data): Product;

    public function delete(int $id): void;
}
