<?php

namespace App\Repositories\Products;

use App\DTOs\Products\ProductData;
use App\DTOs\Products\ProductFilterData;
use App\Interfaces\Cache\CacheKeyGeneratorInterface;
use App\Interfaces\Products\ProductRepositoryInterface;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ProductRepository implements ProductRepositoryInterface
{
    private const CACHE_TAG = 'products';

    public function __construct(
        private readonly CacheKeyGeneratorInterface $cacheKeyGenerator,
    ) {}

    public function paginate(ProductFilterData $filters): LengthAwarePaginator
    {
        $key = $this->cacheKeyGenerator->generate(self::CACHE_TAG . '.index', $filters->toCacheParams());

        return Cache::tags([self::CACHE_TAG])->remember(
            $key,
            now()->addHour(),
            fn () => QueryBuilder::for(Product::class, $this->buildFilterRequest($filters))
                ->where('user_id', $filters->userId)
                ->allowedFilters(
                    AllowedFilter::partial('name'),
                    AllowedFilter::scope('min_price'),
                    AllowedFilter::scope('max_price'),
                    AllowedFilter::scope('min_quantity'),
                    AllowedFilter::scope('max_quantity'),
                )
                ->allowedSorts('name', 'price', 'created_at')
                ->paginate(perPage: $filters->perPage, page: $filters->page),
        );
    }

    /**
     * Build a standalone request carrying only the `filter[...]` params Spatie's
     * QueryBuilder expects, derived from the DTO — kept independent from the
     * real HTTP request so the repository never depends on global request state.
     */
    private function buildFilterRequest(ProductFilterData $filters): Request
    {
        return new Request([
            'filter' => array_filter([
                'name' => $filters->name,
                'min_price' => $filters->minPrice,
                'max_price' => $filters->maxPrice,
                'min_quantity' => $filters->minQuantity,
                'max_quantity' => $filters->maxQuantity,
            ], fn (mixed $value): bool => $value !== null),
        ]);
    }

    public function find(int $id): Product
    {
        $key = $this->cacheKeyGenerator->generate(self::CACHE_TAG . '.find', ['id' => $id]);

        return Cache::tags([self::CACHE_TAG])->remember(
            $key,
            now()->addHour(),
            fn () => Product::query()->findOrFail($id),
        );
    }

    public function create(ProductData $data): Product
    {
        $product = Product::query()->create($data->toArray());

        $this->flushCache();

        return $product;
    }

    public function update(int $id, ProductData $data): Product
    {
        $product = $this->find($id);
        $product->update($data->toArray());

        $this->flushCache();

        return $product->refresh();
    }

    public function delete(int $id): void
    {
        $this->find($id)->delete();

        $this->flushCache();
    }

    private function flushCache(): void
    {
        Cache::tags([self::CACHE_TAG])->flush();
    }
}
