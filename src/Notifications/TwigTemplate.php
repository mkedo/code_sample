<?php

namespace App\Notifications;

/**
 * Шаблонизатор под twig-шаблоны хранящиеся в файловой системе.
 */
class TwigTemplate implements Template
{
    /**
     * @var \Twig\Environment
     */
    private $twig;

    /**
     * TwigTemplate constructor.
     * @param \Twig\Environment $twig
     */
    public function __construct(\Twig\Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * {@inheritdoc}
     */
    public function render(string $view, array $parameters): array
    {
        $twig = $this->twig;
        $tplPath = sprintf("notifications/%s.twig", basename($view));
        $text = $twig->render($tplPath, $parameters);
        // в первой строке заголовок, в остальных текст
        $parts = preg_split("#(\r\n|\r|\n){1,2}#", $text, 2);
        if (count($parts) != 2) {
            throw new \Exception("Неправильный формат шаблона. "
                ."1 строкой должен идти заголовок, затем 1 или 2 переноса строки, затем текст уведомления");
        }
        return [
            'subject' => $parts[0],
            'body' => $parts[1],
        ];
    }
}
