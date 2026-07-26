<?php

namespace Tests\Unit\Services\Products;

use App\DTOs\Products\ProductData;
use App\DTOs\Products\ProductFilterData;
use App\Interfaces\Products\ProductRepositoryInterface;
use App\Models\Product;
use App\Services\Products\ProductService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductServiceTest extends TestCase
{
    #[Test]
    public function it_delegates_listing_to_the_repository(): void
    {
        $filters = new ProductFilterData(userId: 1, name: 'mouse');
        $paginator = Mockery::mock(LengthAwarePaginator::class);

        $repository = $this->mockRepository(function (MockInterface $mock) use ($filters, $paginator) {
            $mock->shouldReceive('paginate')->once()->with($filters)->andReturn($paginator);
        });

        $result = (new ProductService($repository))->list($filters);

        $this->assertSame($paginator, $result);
    }

    #[Test]
    public function it_delegates_find_to_the_repository(): void
    {
        $product = new Product(['name' => 'Mouse']);

        $repository = $this->mockRepository(function (MockInterface $mock) use ($product) {
            $mock->shouldReceive('find')->once()->with(1)->andReturn($product);
        });

        $result = (new ProductService($repository))->find(1);

        $this->assertSame($product, $result);
    }

    #[Test]
    public function it_delegates_creation_to_the_repository(): void
    {
        $data = new ProductData('Mouse', null, 10.0, 5, 1);
        $product = new Product(['name' => 'Mouse']);

        $repository = $this->mockRepository(function (MockInterface $mock) use ($data, $product) {
            $mock->shouldReceive('create')->once()->with($data)->andReturn($product);
        });

        $result = (new ProductService($repository))->create($data);

        $this->assertSame($product, $result);
    }

    #[Test]
    public function it_delegates_update_to_the_repository_by_id(): void
    {
        $product = new Product(['name' => 'Mouse']);
        $data = new ProductData('Mouse Pro', null, 20.0, 3);

        $repository = $this->mockRepository(function (MockInterface $mock) use ($product, $data) {
            $mock->shouldReceive('update')->once()->with(1, $data)->andReturn($product);
        });

        $result = (new ProductService($repository))->update(1, $data);

        $this->assertSame($product, $result);
    }

    #[Test]
    public function it_delegates_deletion_to_the_repository_by_id(): void
    {
        $repository = $this->mockRepository(function (MockInterface $mock) {
            $mock->shouldReceive('delete')->once()->with(1);
        });

        (new ProductService($repository))->delete(1);

        $this->addToAssertionCount(1);
    }

    private function mockRepository(callable $expectations): ProductRepositoryInterface
    {
        /** @var ProductRepositoryInterface&MockInterface $mock */
        $mock = Mockery::mock(ProductRepositoryInterface::class);
        $expectations($mock);

        return $mock;
    }
}
