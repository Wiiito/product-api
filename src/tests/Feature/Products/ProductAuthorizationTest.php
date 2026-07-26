<?php

namespace Tests\Feature\Products;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function a_user_cannot_view_another_users_product(): void
    {
        $owner = User::factory()->create();
        $product = Product::factory()->for($owner)->create();

        Sanctum::actingAs(User::factory()->create());

        $response = $this->getJson("/api/v1/products/{$product->id}");

        $response->assertStatus(404)
            ->assertExactJson(['message' => 'Recurso não encontrado.']);
    }

    #[Test]
    public function a_user_cannot_update_another_users_product(): void
    {
        $owner = User::factory()->create();
        $product = Product::factory()->for($owner)->create(['name' => 'Original']);

        Sanctum::actingAs(User::factory()->create());

        $response = $this->putJson("/api/v1/products/{$product->id}", [
            'name' => 'Hackeado',
            'description' => null,
            'price' => 1,
            'quantity' => 1,
        ]);

        $response->assertStatus(404);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Original']);
    }

    #[Test]
    public function a_user_cannot_delete_another_users_product(): void
    {
        $owner = User::factory()->create();
        $product = Product::factory()->for($owner)->create();

        Sanctum::actingAs(User::factory()->create());

        $response = $this->deleteJson("/api/v1/products/{$product->id}");

        $response->assertStatus(404);
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    #[Test]
    public function a_user_does_not_see_other_users_products_in_the_listing(): void
    {
        $me = Sanctum::actingAs(User::factory()->create());
        Product::factory()->for($me)->create(['name' => 'Meu Produto']);

        $otherUser = User::factory()->create();
        Product::factory()->for($otherUser)->count(2)->create();

        $response = $this->getJson('/api/v1/products');

        $response->assertOk()->assertJsonCount(1, 'data');
        $this->assertSame('Meu Produto', $response->json('data.0.name'));
    }

    #[Test]
    public function creating_a_product_always_assigns_the_authenticated_user_as_owner(): void
    {
        $me = Sanctum::actingAs(User::factory()->create());
        $someoneElse = User::factory()->create();

        $response = $this->postJson('/api/v1/products', [
            'name' => 'Mouse Gamer',
            'description' => null,
            'price' => 10,
            'quantity' => 1,
            'user_id' => $someoneElse->id,
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('products', [
            'name' => 'Mouse Gamer',
            'user_id' => $me->id,
        ]);
        $this->assertDatabaseMissing('products', [
            'name' => 'Mouse Gamer',
            'user_id' => $someoneElse->id,
        ]);
    }

    #[Test]
    public function a_user_can_still_view_update_and_delete_their_own_product(): void
    {
        $me = Sanctum::actingAs(User::factory()->create());
        $product = Product::factory()->for($me)->create();

        $this->getJson("/api/v1/products/{$product->id}")->assertOk();

        $this->putJson("/api/v1/products/{$product->id}", [
            'name' => 'Atualizado',
            'description' => null,
            'price' => 5,
            'quantity' => 1,
        ])->assertOk();

        $this->deleteJson("/api/v1/products/{$product->id}")->assertNoContent();
    }
}
