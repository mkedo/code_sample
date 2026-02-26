<?php

namespace App\Api;

use Exception;

/**
 * Вспомогательный класс для работы с входными данными.
 * Чтобы не писать проверки каждый раз ручками.
 */
class TypedObject
{
    /**
     * @var array
     */
    private $content;

    /**
     * @var string[]
     */
    private $path;

    /**
     * TypedObject constructor.
     * @param array $content произвольные данные
     * @param string[] $path путь до переменной/ключа из которого взяли эти данные.
     * Нужно для генерации более понятных ошибок, в каком ключе произошла ошибка.
     */
    private function __construct(array $content, array $path = [])
    {
        $this->content = $content;
        $this->path = $path;
    }

    /**
     * Создать из массива.
     * @param array $array
     * @return self
     */
    public static function fromArray(array $array): self
    {
        return new self($array);
    }

    /**
     * Создать из json-строки.
     * @param string $jsonString json-строка
     * @return self
     * @throws Exception
     */
    public static function fromJson(string $jsonString): self
    {
        $content = json_decode($jsonString, true);
        if ($content === null) {
            throw new Exception("Не удалось декодировать json: " . json_last_error_msg());
        }
        return new self($content);
    }

    /**
     * Получить значение как объект TypedObject.
     * @param string $key
     * @return self
     * @throws Exception
     */
    public function asTypedObject(string $key): self
    {
        $path = $this->path;
        $path [] = $key;
        $content = $this->asArray($key);
        return new self($content, $path);
    }

    /**
     * Получить параметр.
     * @param string $key
     * @return mixed
     * @throws Exception
     */
    private function get(string $key)
    {
        if (!$this->has($key)) {
            $pathStr = $this->keyPath($key);
            throw new Exception("Отсутствует ожидаемый параметр '{$pathStr}'");
        }
        return $this->content[$key];
    }

    /**
     * Получить скалярное значение по ключу.
     * Будет ошибка, если значение не скаляр.
     * @param string $key
     * @return string|int|float|null
     * @throws Exception
     */
    public function asScalar(string $key)
    {
        $value = $this->get($key);
        if (!is_scalar($value) && !is_null($value)) {
            throw new Exception(
                sprintf(
                    "В '%s' ожидается скаляр, фактически %s",
                    $this->keyPath($key),
                    gettype($value)
                )
            );
        }
        return $value;
    }

    /**
     * Установлен ли параметр с указанным ключом.
     * @param string $key ключ
     * @return bool
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->content);
    }

    /**
     * Получить массив по ключу.
     * Будет ошибка, если значение не массив.
     * @param string $key
     * @return array
     * @throws Exception
     */
    public function asArray(string $key): array
    {
        $array = $this->get($key);
        if (!is_array($array)) {
            throw new Exception(
                sprintf(
                    "В '%s' ожидается массив, фактически %s",
                    $this->keyPath($key),
                    gettype($array)
                )
            );
        }
        return $array;
    }

    /**
     * Перевести обратно в массив.
     * @return array
     */
    public function toArray(): array
    {
        return $this->content;
    }

    /**
     * Формирует полный путь до параметра.
     * @param string $key
     * @return string
     */
    private function keyPath(string $key): string
    {
        $path = $this->path;
        $path [] = $key;
        return implode("/", $path);
    }
}
