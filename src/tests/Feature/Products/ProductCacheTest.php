<?php

namespace Tests\Feature\Products;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductCacheTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function the_second_identical_listing_call_is_served_from_cache(): void
    {
        $user = Sanctum::actingAs(User::factory()->create());
        Product::factory()->for($user)->count(2)->create();

        $this->getJson('/api/v1/products')->assertOk()->assertJsonCount(2, 'data');

        $queries = 0;
        DB::listen(function () use (&$queries) {
            $queries++;
        });

        $this->getJson('/api/v1/products')->assertOk()->assertJsonCount(2, 'data');

        $this->assertSame(0, $queries, 'The second identical listing call should not hit the database.');
    }

    #[Test]
    public function the_second_identical_show_call_is_served_from_cache(): void
    {
        $user = Sanctum::actingAs(User::factory()->create());
        $product = Product::factory()->for($user)->create();

        $this->getJson("/api/v1/products/{$product->id}")->assertOk();

        $queries = 0;
        DB::listen(function () use (&$queries) {
            $queries++;
        });

        $this->getJson("/api/v1/products/{$product->id}")->assertOk();

        $this->assertSame(0, $queries, 'The second identical show call should not hit the database.');
    }

    #[Test]
    public function creating_a_product_invalidates_the_listing_cache(): void
    {
        $user = Sanctum::actingAs(User::factory()->create());
        Product::factory()->for($user)->create();

        $this->getJson('/api/v1/products')->assertOk()->assertJsonCount(1, 'data');

        $this->postJson('/api/v1/products', [
            'name' => 'Novo Produto',
            'description' => null,
            'price' => 20,
            'quantity' => 1,
        ])->assertCreated();

        $this->getJson('/api/v1/products')->assertOk()->assertJsonCount(2, 'data');
    }

    #[Test]
    public function updating_a_product_invalidates_the_listing_cache(): void
    {
        $user = Sanctum::actingAs(User::factory()->create());
        $product = Product::factory()->for($user)->create(['name' => 'Antigo']);

        $this->getJson('/api/v1/products?name=Antigo')->assertOk()->assertJsonCount(1, 'data');

        $this->putJson("/api/v1/products/{$product->id}", [
            'name' => 'Renomeado',
            'description' => null,
            'price' => 30,
            'quantity' => 2,
        ])->assertOk();

        $this->getJson('/api/v1/products?name=Antigo')->assertOk()->assertJsonCount(0, 'data');
    }

    #[Test]
    public function deleting_a_product_invalidates_the_listing_cache(): void
    {
        $user = Sanctum::actingAs(User::factory()->create());
        $product = Product::factory()->for($user)->create();

        $this->getJson('/api/v1/products')->assertOk()->assertJsonCount(1, 'data');

        $this->deleteJson("/api/v1/products/{$product->id}")->assertNoContent();

        $this->getJson('/api/v1/products')->assertOk()->assertJsonCount(0, 'data');
    }
}
