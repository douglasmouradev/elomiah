<?php

declare(strict_types=1);

namespace App\Core;

final class View
{
    public static function render(string $template, array $data = [], ?string $layout = null): string
    {
        $content = self::renderFile($template, $data);
        if ($layout === null) {
            return $content;
        }
        $data['content'] = $content;
        return self::renderFile($layout, $data);
    }

    public static function renderFile(string $template, array $data = []): string
    {
        $path = APP_PATH . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $template) . '.php';
        if (!is_file($path)) {
            throw new \RuntimeException('View não encontrada: ' . $template);
        }
        extract($data, EXTR_SKIP);
        ob_start();
        require $path;
        return (string) ob_get_clean();
    }

    public static function partial(string $template, array $data = []): void
    {
        echo self::renderFile($template, $data);
    }
}
