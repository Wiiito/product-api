<?php

namespace App\Services\Products;

use App\DTOs\Products\ProductData;
use App\DTOs\Products\ProductFilterData;
use App\Interfaces\Products\ProductRepositoryInterface;
use App\Interfaces\Products\ProductServiceInterface;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductService implements ProductServiceInterface
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
    ) {}

    public function list(ProductFilterData $filters): LengthAwarePaginator
    {
        return $this->products->paginate($filters);
    }

    public function find(int $id): Product
    {
        return $this->products->find($id);
    }

    public function create(ProductData $data): Product
    {
        return $this->products->create($data);
    }

    public function update(int $id, ProductData $data): Product
    {
        return $this->products->update($id, $data);
    }

    public function delete(int $id): void
    {
        $this->products->delete($id);
    }
}
