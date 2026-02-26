<?php

namespace App\Api;

use Symfony\Component\HttpFoundation\Request;

/**
 * Удобный класс для работы с данными передаваемыми в json.
 */
class JsonRequest
{
    /**
     * @var Request
     */
    private $request;

    /**
     * JsonRequest constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Достать массив из json.
     * @param string $key
     * @return array
     * @throws \Exception
     */
    public function getArray(string $key): array
    {
        $json = $this->request->get($key);
        if (empty($json)) {
            throw new \Exception("Нет ключа $key");
        }
        $array = json_decode($json, true);
        if ($array === null) {
            throw new \Exception("Не удалось декодировать json");
        }
        return $array;
    }
}
