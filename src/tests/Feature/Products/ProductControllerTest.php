<?php

namespace Tests\Feature\Products;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function a_guest_cannot_access_products(): void
    {
        $this->getJson('/api/v1/products')->assertUnauthorized();
    }

    #[Test]
    public function an_authenticated_user_can_create_a_product(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->postJson('/api/v1/products', [
            'name' => 'Mouse Gamer',
            'description' => 'Mouse otico',
            'price' => 99.9,
            'quantity' => 10,
        ]);

        $response->assertCreated()->assertJsonPath('data.name', 'Mouse Gamer');
        $this->assertDatabaseHas('products', ['name' => 'Mouse Gamer']);
    }

    #[Test]
    public function creating_a_product_requires_valid_data(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->postJson('/api/v1/products', ['price' => -1]);

        $response->assertUnprocessable()->assertJsonValidationErrors(['name', 'price', 'quantity']);
    }

    #[Test]
    public function an_authenticated_user_can_list_products(): void
    {
        $user = Sanctum::actingAs(User::factory()->create());
        Product::factory()->for($user)->count(3)->create();

        $response = $this->getJson('/api/v1/products');

        $response->assertOk()->assertJsonCount(3, 'data');
    }

    #[Test]
    public function an_authenticated_user_can_view_a_single_product(): void
    {
        $user = Sanctum::actingAs(User::factory()->create());
        $product = Product::factory()->for($user)->create();

        $response = $this->getJson("/api/v1/products/{$product->id}");

        $response->assertOk()->assertJson([
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
            ],
        ]);
    }

    #[Test]
    public function viewing_a_missing_product_returns_404(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/v1/products/999')->assertNotFound();
    }

    #[Test]
    public function an_authenticated_user_can_update_a_product(): void
    {
        $user = Sanctum::actingAs(User::factory()->create());
        $product = Product::factory()->for($user)->create();

        $response = $this->putJson("/api/v1/products/{$product->id}", [
            'name' => 'Mouse Gamer Pro',
            'description' => 'Atualizado',
            'price' => 150,
            'quantity' => 3,
        ]);

        $response->assertOk()->assertJsonPath('data.name', 'Mouse Gamer Pro');
        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Mouse Gamer Pro']);
    }

    #[Test]
    public function an_authenticated_user_can_delete_a_product(): void
    {
        $user = Sanctum::actingAs(User::factory()->create());
        $product = Product::factory()->for($user)->create();

        $response = $this->deleteJson("/api/v1/products/{$product->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}
