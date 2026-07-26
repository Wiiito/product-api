<?php

namespace Tests\Unit\Repositories\Products;

use App\DTOs\Products\ProductData;
use App\DTOs\Products\ProductFilterData;
use App\Models\Product;
use App\Models\User;
use App\Repositories\Products\ProductRepository;
use App\Services\Cache\CacheKeyGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ProductRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new ProductRepository(new CacheKeyGenerator);
    }

    #[Test]
    public function it_caches_the_paginated_listing(): void
    {
        $user = User::factory()->create();
        Product::factory()->for($user)->create(['name' => 'Mouse']);

        $filters = new ProductFilterData(userId: $user->id);

        $first = $this->repository->paginate($filters);
        Product::factory()->for($user)->create(['name' => 'Teclado']);
        $second = $this->repository->paginate($filters);

        $this->assertSame(1, $first->total());
        $this->assertSame(1, $second->total(), 'Listing should be served from cache and stay stale until invalidated.');
    }

    #[Test]
    public function creating_a_product_invalidates_the_listing_cache(): void
    {
        $user = User::factory()->create();
        $filters = new ProductFilterData(userId: $user->id);

        $this->repository->paginate($filters);

        $this->repository->create(new ProductData('Mouse', null, 99.9, 10, $user->id));

        $this->assertSame(1, $this->repository->paginate($filters)->total());
    }

    #[Test]
    public function updating_a_product_invalidates_the_listing_cache(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->for($user)->create(['name' => 'Mouse']);
        $filters = new ProductFilterData(userId: $user->id, name: 'Mouse');

        $this->assertSame(1, $this->repository->paginate($filters)->total());

        $this->repository->update($product->id, new ProductData('Teclado', null, 50, 5));

        $this->assertSame(0, $this->repository->paginate($filters)->total());
    }

    #[Test]
    public function it_only_lists_products_belonging_to_the_given_user(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        Product::factory()->for($owner)->create();
        Product::factory()->for($otherUser)->count(2)->create();

        $result = $this->repository->paginate(new ProductFilterData(userId: $owner->id));

        $this->assertSame(1, $result->total());
    }

    #[Test]
    public function updating_a_product_does_not_change_its_owner(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->for($user)->create();

        $this->repository->update($product->id, new ProductData('Renomeado', null, 10, 1));

        $this->assertSame($user->id, $product->refresh()->user_id);
    }

    #[Test]
    public function deleting_a_product_invalidates_the_listing_cache(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->for($user)->create();
        $filters = new ProductFilterData(userId: $user->id);

        $this->assertSame(1, $this->repository->paginate($filters)->total());

        $this->repository->delete($product->id);

        $this->assertSame(0, $this->repository->paginate($filters)->total());
    }
}
