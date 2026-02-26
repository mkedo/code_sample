<?php

namespace App\Api;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Ответ-ошибка.
 */
class ApiError extends JsonResponse
{
    /**
     * @var array
     */
    private $errorData;

    /**
     * ApiError constructor.
     * @param string $message
     */
    public function __construct(string $message)
    {
        $this->errorData = [
            'error' => [
                'message' => $message,
            ],
        ];
        parent::__construct($this->errorData);
    }

    /**
     * Добавить стек-трейс к ошибке.
     * @param array $stackTrace
     * @return self
     */
    public function setTrace(array $stackTrace): self
    {
        $this->errorData['trace'] = $stackTrace;
        $this->setData($this->errorData);
        return $this;
    }
}
