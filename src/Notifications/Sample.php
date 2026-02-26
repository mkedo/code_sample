<?php

namespace App\Notifications;

/**
 * Пример уведомления.
 */
class Sample extends Notification
{
    /**
     * Sample constructor.
     */
    public function __construct()
    {
        parent::__construct("sample");
    }

    /**
     * @param string $regNum
     * @return $this
     */
    public function setRegNum(string $regNum): self
    {
        $this->addParameter('contract_regnum', $regNum);
        return $this;
    }

    /**
     * @param \DateTime $time
     * @return $this
     */
    public function setTime(\DateTime $time): self
    {
        $this->addParameter('time', $time);
        return $this;
    }
}
