<?php

namespace App\Notifications;

/**
 * Шаблонизатор уведомлений.
 */
interface Template
{
    /**
     * Сформировать уведомление.
     * @param string $view наименований шаблона
     * @param array $parameters параметры шаблона
     * @return array
     * [
     *  'subject' =>
     *  'body' =>
     * ]
     */
    public function render(string $view, array $parameters): array;
}
