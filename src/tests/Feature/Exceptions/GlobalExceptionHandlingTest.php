<?php

namespace Tests\Feature\Exceptions;

use App\DTOs\Products\ProductData;
use App\DTOs\Products\ProductFilterData;
use App\Interfaces\Products\ProductRepositoryInterface;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;

class GlobalExceptionHandlingTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function unauthenticated_requests_return_401_in_portuguese(): void
    {
        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(401)->assertExactJson(['message' => 'Não autenticado.']);
    }

    #[Test]
    public function missing_resources_return_404_in_portuguese_without_leaking_internals(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->getJson('/api/v1/products/999');

        $response->assertStatus(404)->assertExactJson(['message' => 'Recurso não encontrado.']);
        $this->assertStringNotContainsString('No query results', $response->getContent());
    }

    #[Test]
    public function unknown_routes_return_404_in_portuguese(): void
    {
        $response = $this->getJson('/api/v1/rota-que-nao-existe');

        $response->assertStatus(404)->assertExactJson(['message' => 'Recurso não encontrado.']);
    }

    #[Test]
    public function validation_failures_return_422_in_portuguese(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->postJson('/api/v1/products', []);

        $response->assertStatus(422);
        $data = $response->json();
        $this->assertSame('Os dados fornecidos são inválidos.', $data['message']);
        $this->assertSame('O campo nome é obrigatório.', $data['errors']['name'][0]);
        $this->assertSame('O campo preço é obrigatório.', $data['errors']['price'][0]);
    }

    #[Test]
    public function unexpected_errors_return_a_generic_500_without_leaking_the_real_message(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->app->bind(ProductRepositoryInterface::class, function () {
            return new class implements ProductRepositoryInterface
            {
                public function paginate(ProductFilterData $filters): LengthAwarePaginator
                {
                    throw new RuntimeException('SQLSTATE[42703]: column "supersecret" does not exist');
                }

                public function find(int $id): Product
                {
                    throw new RuntimeException('unused');
                }

                public function create(ProductData $data): Product
                {
                    throw new RuntimeException('unused');
                }

                public function update(int $id, ProductData $data): Product
                {
                    throw new RuntimeException('unused');
                }

                public function delete(int $id): void
                {
                    throw new RuntimeException('unused');
                }
            };
        });

        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(500)->assertExactJson(['message' => 'Ocorreu um erro inesperado.']);
        $this->assertStringNotContainsString('SQLSTATE', $response->getContent());
        $this->assertStringNotContainsString('supersecret', $response->getContent());
    }
}
