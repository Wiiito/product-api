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
use App\Models\Product;
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

    public function show(Product $product): ProductResource
    {
        $this->authorize('view', $product);

        return ProductResource::make($this->products->find($product->id));
    }

    public function update(UpdateProductRequest $request, Product $product): ProductResource
    {
        $this->authorize('update', $product);

        return ProductResource::make(
            $this->products->update($product->id, ProductData::fromArray($request->validated())),
        );
    }

    public function destroy(Product $product): Response
    {
        $this->authorize('delete', $product);

        $this->products->delete($product->id);

        return response()->noContent();
    }
}
