<?php

namespace App\Http\Controllers\Api\V1\Products;

use App\DTOs\Products\ProductData;
use App\DTOs\Products\ProductFilterData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Products\IndexProductRequest;
use App\Http\Requests\Products\StoreProductRequest;
use App\Http\Requests\Products\UpdateProductRequest;
use App\Http\Resources\Products\ProductResource;
use App\Interfaces\Products\ProductServiceInterface;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductServiceInterface $products,
    ) {}

    public function index(IndexProductRequest $request): AnonymousResourceCollection
    {
        return ProductResource::collection(
            $this->products->list(ProductFilterData::fromArray($request->validated(), $request->user()->id)),
        );
    }

    public function store(StoreProductRequest $request): ProductResource
    {
        return ProductResource::make(
            $this->products->create(ProductData::fromArray($request->validated(), $request->user()->id)),
        );
    }

    public function show(int $productId): ProductResource
    {
        $product = $this->products->find($productId);

        $this->authorize('view', $product);

        return ProductResource::make($product);
    }

    public function update(UpdateProductRequest $request, int $productId): ProductResource
    {
        $existingProduct = $this->products->find($productId);

        $this->authorize('update', $existingProduct);

        return ProductResource::make(
            $this->products->update($existingProduct->id, ProductData::fromArray($request->validated())),
        );
    }

    public function destroy(int $productId): Response
    {
        $existingProduct = $this->products->find($productId);

        $this->authorize('delete', $existingProduct);

        $this->products->delete($existingProduct->id);

        return response()->noContent();
    }
}
