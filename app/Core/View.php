<?php

declare(strict_types=1);

namespace App\Core;

final class View
{
    /** Rendert app/views/{$view}.php binnen de hoofdlayout. */
    public static function render(string $view, array $data = [], int $status = 200): void
    {
        http_response_code($status);
        $content = self::capture($view, $data);
        echo self::capture('layout/main', $data + ['content' => $content]);
    }

    /** Rendert een los onderdeel (partial) en geeft de HTML terug. */
    public static function partial(string $view, array $data = []): string
    {
        return self::capture('partials/' . $view, $data);
    }

    private static function capture(string $view, array $data): string
    {
        $file = APP_ROOT . '/app/views/' . $view . '.php';
        if (!is_file($file)) {
            throw new \RuntimeException("View niet gevonden: {$view}");
        }
        extract($data, EXTR_SKIP);
        ob_start();
        require $file;
        return (string) ob_get_clean();
    }
}
