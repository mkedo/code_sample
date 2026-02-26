<?php

namespace App\Api;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Успешный ответ от сервера.
 */
class ApiResponse extends JsonResponse
{
    /**
     * ApiResponse constructor.
     * @param mixed $response
     */
    public function __construct($response)
    {
        parent::__construct([
            'response' => $response
        ]);
    }
}
