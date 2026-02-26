<?php

namespace App\DbConfig;
use App\Repository\ConfigRepository;

/**
 * Решает следующую проблему:
 * Чтоб загрузить параметры конфигов в базу нужно либо запустить комманду, либо загрузить через сайт.
 * Но для этого нужно проинициализировать все сервисы, включая те для которых еще не загружены конфиги.
 * Откладываем загрузку параметра до того как они рально понадобятся.
 */
class DeferredParam
{
    /**
     * @var ConfigRepository
     */
    private $configRepo;

    /**
     * @var string
     */
    private $paramCode;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @var bool
     */
    private $valueLoaded;

    /**
     * @param ConfigRepository $configRepo
     * @param string $paramCode
     */
    public function __construct(ConfigRepository $configRepo, string $paramCode)
    {
        $this->configRepo = $configRepo;
        $this->paramCode = $paramCode;
    }

    /**
     * Получить не null-вое значение.
     * @return mixed
     * @throws \Exception
     */
    public function get()
    {
        if (!$this->valueLoaded) {
            $this->value = $this->configRepo->getValueByCode($this->paramCode);
            $this->valueLoaded = true;
        }
        if ($this->value === null || $this->value === '') {
            throw new \Exception("Значение параметра {$this->paramCode} не установлено");
        }
        return $this->value;
    }

    /**
     * Получить значение или null.
     * @return mixed|null
     * @throws \Exception
     */
    public function getOrNull()
    {
        if (!$this->valueLoaded) {
            $this->value = $this->configRepo->getValueByCode($this->paramCode);
            $this->valueLoaded = true;
        }
        return $this->value;
    }
}
