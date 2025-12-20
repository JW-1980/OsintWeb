<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * Base controller providing common functionality for all controllers
 */
abstract class Controller extends BaseController
{
    use AuthorizesRequests;
    use ValidatesRequests;

    /**
     * Default pagination size
     */
    protected int $perPage = 25;

    /**
     * Get the authenticated user
     *
     * @return \App\Models\User|null
     */
    protected function user(): mixed
    {
        return auth()->user();
    }

    /**
     * Return a success response
     *
     * @param mixed $data
     * @param int $status
     * @return \Illuminate\Http\JsonResponse
     */
    protected function success(mixed $data, int $status = 200): \Illuminate\Http\JsonResponse
    {
        return response()->json(['data' => $data], $status);
    }

    /**
     * Return a paginated response
     *
     * @param \Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator
     * @return \Illuminate\Http\JsonResponse
     */
    protected function paginated(\Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'data' => $paginator->items(),
            'meta' => [
                'pagination' => [
                    'total' => $paginator->total(),
                    'count' => $paginator->count(),
                    'per_page' => $paginator->perPage(),
                    'current_page' => $paginator->currentPage(),
                    'total_pages' => $paginator->lastPage(),
                ],
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
        ]);
    }

    /**
     * Return an error response
     *
     * @param string $message
     * @param int $status
     * @param array $errors
     * @return \Illuminate\Http\JsonResponse
     */
    protected function error(string $message, int $status = 400, array $errors = []): \Illuminate\Http\JsonResponse
    {
        $response = ['message' => $message];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }

    /**
     * Return a not found response
     *
     * @param string $resource
     * @return \Illuminate\Http\JsonResponse
     */
    protected function notFound(string $resource = 'Resource'): \Illuminate\Http\JsonResponse
    {
        return $this->error("{$resource} not found", 404);
    }

    /**
     * Return an unauthorized response
     *
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function unauthorized(string $message = 'Unauthorized'): \Illuminate\Http\JsonResponse
    {
        return $this->error($message, 401);
    }

    /**
     * Return a forbidden response
     *
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function forbidden(string $message = 'Forbidden'): \Illuminate\Http\JsonResponse
    {
        return $this->error($message, 403);
    }
}
