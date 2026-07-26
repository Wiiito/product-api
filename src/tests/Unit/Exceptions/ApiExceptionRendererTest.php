<?php

namespace Tests\Unit\Exceptions;

use App\Exceptions\ApiExceptionRenderer;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Testing\TestResponse;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Tests\TestCase;

class ApiExceptionRendererTest extends TestCase
{
    private ApiExceptionRenderer $renderer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->renderer = new ApiExceptionRenderer;
    }

    #[Test]
    public function it_ignores_requests_that_do_not_want_json(): void
    {
        $request = Request::create('/web-page', 'GET');

        $response = ($this->renderer)(new RuntimeException('boom'), $request);

        $this->assertNull($response);
    }

    #[Test]
    public function it_maps_authentication_exception_to_401(): void
    {
        $response = $this->invoke(new AuthenticationException);

        $response->assertStatus(401);
        $this->assertSame('Não autenticado.', $response->getData(true)['message']);
    }

    #[Test]
    public function it_maps_authorization_exception_to_403(): void
    {
        $response = $this->invoke(new AuthorizationException);

        $response->assertStatus(403);
        $this->assertSame('Você não tem permissão para executar esta ação.', $response->getData(true)['message']);
    }

    #[Test]
    public function it_maps_access_denied_http_exception_to_403(): void
    {
        $response = $this->invoke(new AccessDeniedHttpException);

        $response->assertStatus(403);
        $this->assertSame('Você não tem permissão para executar esta ação.', $response->getData(true)['message']);
    }

    #[Test]
    public function it_maps_model_not_found_exception_to_404(): void
    {
        $response = $this->invoke(new ModelNotFoundException('No query results for model [App\\Models\\Product] 999'));

        $response->assertStatus(404);
        $this->assertSame('Recurso não encontrado.', $response->getData(true)['message']);
    }

    #[Test]
    public function it_maps_not_found_http_exception_to_404(): void
    {
        $response = $this->invoke(new NotFoundHttpException);

        $response->assertStatus(404);
        $this->assertSame('Recurso não encontrado.', $response->getData(true)['message']);
    }

    #[Test]
    public function it_maps_method_not_allowed_exception_to_405(): void
    {
        $response = $this->invoke(new MethodNotAllowedHttpException(['GET']));

        $response->assertStatus(405);
        $this->assertSame('Método não permitido.', $response->getData(true)['message']);
    }

    #[Test]
    public function it_maps_throttle_exception_to_429(): void
    {
        $response = $this->invoke(new TooManyRequestsHttpException);

        $response->assertStatus(429);
        $this->assertSame('Muitas requisições. Tente novamente mais tarde.', $response->getData(true)['message']);
    }

    #[Test]
    public function it_maps_validation_exception_to_422_with_field_errors(): void
    {
        $validator = validator([], ['name' => 'required']);
        $validator->fails();

        $response = $this->invoke(new ValidationException($validator));

        $response->assertStatus(422);
        $data = $response->getData(true);
        $this->assertSame('Os dados fornecidos são inválidos.', $data['message']);
        $this->assertArrayHasKey('name', $data['errors']);
    }

    #[Test]
    public function it_maps_any_unexpected_exception_to_a_generic_500_without_leaking_its_message(): void
    {
        $response = $this->invoke(new RuntimeException('SQLSTATE[42703]: sensitive internal detail'));

        $response->assertStatus(500);
        $data = $response->getData(true);
        $this->assertSame('Ocorreu um erro inesperado.', $data['message']);
        $this->assertStringNotContainsString('SQLSTATE', json_encode($data));
        $this->assertStringNotContainsString('sensitive internal detail', json_encode($data));
    }

    private function invoke(\Throwable $e): TestResponse
    {
        $request = Request::create('/api/v1/products', 'GET');
        $request->headers->set('Accept', 'application/json');

        return TestResponse::fromBaseResponse(($this->renderer)($e, $request));
    }
}
