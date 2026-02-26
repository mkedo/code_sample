<?php

namespace App\DbConfig;

use App\Repository\ConfigRepository;

/**
 * Класс для того чтобы доставать параметры при описании в services.yaml
 */
class LookupParam
{
    /**
     * @var ConfigRepository
     */
    private $configRepo;

    /**
     * @param ConfigRepository $configRepo
     */
    public function __construct(ConfigRepository $configRepo)
    {
        $this->configRepo = $configRepo;
    }

    /**
     * @param string $paramCode
     * @return DeferredParam
     */
    public function get(string $paramCode): DeferredParam
    {
        return new DeferredParam($this->configRepo, $paramCode);
    }
}
