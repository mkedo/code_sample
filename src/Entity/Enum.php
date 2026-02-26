<?php

namespace App\Entity;

/**
 * Класс для работы с перечислениями.
 * Для создания своего перечисления нужно отнаследоваться от этого класса и перекрыть (override)
 * свойствой $valueMap.
 * (Phan лезет анализировать код в этом комменте и ругается на константы. Отучить его от этого не смог.
 *  Пробел после self нужно будет удалить ручками.)
 * <code>
 * class MyEnum extends Enum {
 *  const OPT_A = 1;
 *  const OPT_B = 2;
 *  protected static $valueMap = [
 *     self ::OPT_A => 'Опция А',
 *     self ::OPT_B => 'Опция Б',
 *  ];
 * }
 * </code>
 */
abstract class Enum
{
    /**
     * Карта значение => наименование.
     * Заполняется наследуемым классом.
     * @var array
     */
    protected static $valueMap = [];

    /**
     * Текущее значение перечисления.
     * @var mixed
     */
    protected $value = null;

    /**
     * Enum constructor.
     * @param mixed $value
     * @throws \Exception
     */
    public function __construct($value)
    {
        if (empty(static::$valueMap)) {
            throw new \Exception("Перечисление не может быть пустым. Заполните \$valueMap");
        }
        $this->setValue($value);
    }

    /**
     * Необходимо для запросов Criteria, иначе записи по полям Enum не будут находиться
     *
     * @return mixed
     */
    public function __toString()
    {
        return $this->asValueOrDefault('');
    }

    /**
     * Выставить значение.
     * @param mixed $value
     * @throws \Exception
     */
    private function setValue($value)
    {
        if ($value !== null && !$this->hasOption($value)) {
            throw new \Exception("Значение '{$value}' не установлено в перечислении");
        }
        $this->value = $value;
    }

    /**
     * Вернуть текстовое представление (название) значения.
     * Если не установлено - будет ошибка.
     * @return string
     * @throws \Exception
     */
    public function asText(): string
    {
        if (!$this->isDefined()) {
            throw new \Exception("Значение перечисления не установлено");
        }
        return static::$valueMap[$this->value];
    }

    /**
     * Вернуть текстовое представление (название) значения.
     * Или текст по умолчанию, если не установлено.
     * @param string $default
     * @return string
     */
    public function asTextOrDefault($default = ''): string
    {
        if (!$this->isDefined()) {
            return $default;
        }
        return static::$valueMap[$this->value];
    }

    /**
     * Вернуть установленное значение или значение по-умолчанию, если значение не установлено.
     * @param mixed $defaultValue
     * @return mixed
     */
    public function asValueOrDefault($defaultValue)
    {
        if (!$this->isDefined()) {
            return $defaultValue;
        }
        return $this->value;
    }

    /**
     * Проверить установлен ли перечисление в это значение.
     * @param mixed|null $value
     * @return bool
     */
    public function isDefinedAs($value): bool
    {
        return $this->value === $value;
    }

    /**
     * Проверить установлено ли перечисление в одно из указанных значений.
     * @param array $values
     * @return bool
     */
    public function isOneOf(array $values): bool
    {
        foreach ($values as $value) {
            if ($this->isDefinedAs($value)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Значение перечисления.
     * Если не установлено - будет ошибка.
     * @return mixed
     * @throws \Exception
     */
    public function asValue()
    {
        if (!$this->isDefined()) {
            throw new \Exception("Значение перечисления не установлено");
        }
        return $this->value;
    }

    /**
     * Установлено ли значение перечисления.
     * @return bool
     */
    public function isDefined(): bool
    {
        return array_key_exists($this->value, static::$valueMap);
    }

    /**
     * Можно ли выставлять такое значение.
     * @param mixed $value проверяемое значение
     * @return bool
     */
    public function hasOption($value): bool
    {
        return array_key_exists($value, static::$valueMap);
    }

    /**
     * Список доступных опций.
     * @return array [ значение => название ]
     */
    public static function options(): array
    {
        return static::$valueMap;
    }
}
