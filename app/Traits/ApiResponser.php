<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponser
{
    /**
     * Return a success JSON response.
     *
     * @param mixed $data
     * @param string|null $message
     * @param int $code
     * @return JsonResponse
     */
    protected function success($data = null, string $message = null, int $code = 200): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    /**
     * Return an error JSON response.
     *
     * @param string|null $message
     * @param int $code
     * @param mixed $data
     * @return JsonResponse
     */
    protected function error(string $message = null, int $code = 400, $data = null): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    /**
     * Return a validation error JSON response.
     *
     * @param mixed $errors
     * @param string $message
     * @return JsonResponse
     */
    protected function validationError($errors, string $message = 'Validation Error', int $code = 422): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'errors' => $errors
        ], $code);
    }
}
