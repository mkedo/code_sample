<?php

namespace App\Notifications;

use App\Entity\Plan;

/**
 * Уведомление.
 */
class Notification
{
    /**
     * @var string
     */
    private $view;

    /**
     * @var array
     */
    private $parameters = [];

    /**
     * План к которому относится уведомление.
     * @var ?Plan
     */
    private $plan;

    /**
     * Notification constructor.
     * @param string $view наименование шаблона (он же код шаблона)
     */
    public function __construct(string $view)
    {
        $this->view = $view;
    }

    /**
     * @param string $name
     * @param $value
     * @return static
     */
    protected function addParameter(string $name, $value)
    {
        $this->parameters[$name] = $value;
        return $this;
    }

    /**
     * Сформировать уведомление.
     * @param Template $template
     * @return array
     * [
     *  'subject' =>
     *  'body' =>
     * ]
     */
    public function format(Template $template): array
    {
        return $template->render($this->view, $this->parameters);
    }

    /**
     * @return Plan|null
     */
    public function getPlan(): ?Plan
    {
        return $this->plan;
    }

    /**
     * @param Plan|null $plan
     */
    public function setPlan(?Plan $plan): void
    {
        $this->plan = $plan;
    }
}
