<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Throwable;

/**
 * Maps every exception raised on JSON/API requests to a consistent, PT-BR JSON
 * body — never the raw exception message for unexpected errors, so internals
 * (SQL, stack traces, third-party error text) never reach the client.
 */
class ApiExceptionRenderer
{
    public function __invoke(Throwable $e, Request $request): ?JsonResponse
    {
        if (! $this->wantsJson($request)) {
            return null;
        }

        return match (true) {
            $e instanceof ValidationException => $this->validationResponse($e),
            $e instanceof AuthenticationException => $this->response('Não autenticado.', 401),
            $e instanceof AuthorizationException, $e instanceof AccessDeniedHttpException => $this->response(
                'Você não tem permissão para executar esta ação.',
                403,
            ),
            $e instanceof ModelNotFoundException, $e instanceof NotFoundHttpException => $this->response(
                'Recurso não encontrado.',
                404,
            ),
            $e instanceof MethodNotAllowedHttpException => $this->response('Método não permitido.', 405),
            $e instanceof TooManyRequestsHttpException => $this->response(
                'Muitas requisições. Tente novamente mais tarde.',
                429,
            ),
            default => $this->response('Ocorreu um erro inesperado.', 500),
        };
    }

    private function wantsJson(Request $request): bool
    {
        return $request->is('api/*') || $request->expectsJson();
    }

    private function validationResponse(ValidationException $e): JsonResponse
    {
        return response()->json([
            'message' => 'Os dados fornecidos são inválidos.',
            'errors' => $e->errors(),
        ], 422);
    }

    private function response(string $message, int $status): JsonResponse
    {
        return response()->json(['message' => $message], $status);
    }
}
