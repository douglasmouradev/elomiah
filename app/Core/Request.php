<?php

declare(strict_types=1);

namespace App\Core;

final class Request
{
    public function method(): string
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        if ($method === 'POST' && isset($_POST['_method'])) {
            $override = strtoupper((string) $_POST['_method']);
            if (in_array($override, ['PUT', 'PATCH', 'DELETE'], true)) {
                return $override;
            }
        }
        return $method;
    }

    public function path(): string
    {
        $fromQuery = $this->input('__path');
        if (is_string($fromQuery) && $fromQuery !== '') {
            $uri = '/' . ltrim(rawurldecode($fromQuery), '/');
            return rtrim($uri, '/') ?: '/';
        }

        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        // Evita tratar /index.php como rota
        if (str_ends_with($uri, '/index.php')) {
            $uri = substr($uri, 0, -strlen('/index.php')) ?: '/';
        }
        $script = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        if ($script !== '/' && $script !== '\\' && str_starts_with($uri, $script)) {
            $uri = substr($uri, strlen($script)) ?: '/';
        }
        $prefix = app_base_path();
        if ($prefix !== '' && ($uri === $prefix || str_starts_with($uri, $prefix . '/'))) {
            $uri = substr($uri, strlen($prefix)) ?: '/';
        }
        return rtrim($uri, '/') ?: '/';
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($_GET, $_POST);
    }

    public function file(string $key): ?array
    {
        return $_FILES[$key] ?? null;
    }

    public function ip(): string
    {
        return client_ip();
    }

    public function userAgent(): string
    {
        return substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);
    }

    public function isAjax(): bool
    {
        return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
    }
}
