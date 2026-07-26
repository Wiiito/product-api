<?php

namespace Tests\Feature\Products;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductFilterTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_filters_products_by_name_and_price_range(): void
    {
        $user = Sanctum::actingAs(User::factory()->create());

        Product::factory()->for($user)->create(['name' => 'Mouse Gamer', 'price' => 99.9]);
        Product::factory()->for($user)->create(['name' => 'Mousepad Grande', 'price' => 60]);
        Product::factory()->for($user)->create(['name' => 'Teclado Mecanico', 'price' => 250]);
        Product::factory()->for($user)->create(['name' => 'Mouse Barato', 'price' => 10]);

        $response = $this->getJson('/api/v1/products?name=mouse&min_price=50&max_price=200');

        $response->assertOk()->assertJsonCount(2, 'data');
        $names = collect($response->json('data'))->pluck('name')->all();
        $this->assertEqualsCanonicalizing(['Mouse Gamer', 'Mousepad Grande'], $names);
    }

    #[Test]
    public function it_filters_products_by_stock_quantity_range(): void
    {
        $user = Sanctum::actingAs(User::factory()->create());

        Product::factory()->for($user)->create(['name' => 'Baixo Estoque', 'quantity' => 2]);
        Product::factory()->for($user)->create(['name' => 'Estoque Medio', 'quantity' => 15]);
        Product::factory()->for($user)->create(['name' => 'Estoque Alto', 'quantity' => 100]);

        $response = $this->getJson('/api/v1/products?min_quantity=10&max_quantity=50');

        $response->assertOk()->assertJsonCount(1, 'data');
        $this->assertSame('Estoque Medio', $response->json('data.0.name'));
    }

    #[Test]
    public function it_returns_all_products_when_no_filters_are_given(): void
    {
        $user = Sanctum::actingAs(User::factory()->create());
        Product::factory()->for($user)->count(2)->create();

        $this->getJson('/api/v1/products')->assertOk()->assertJsonCount(2, 'data');
    }
}
