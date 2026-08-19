<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected function view(string $template, array $data = [], ?string $layout = 'layouts/public'): never
    {
        Response::html(View::render($template, $data, $layout));
    }

    protected function json(array $data, int $code = 200): never
    {
        Response::json($data, $code);
    }

    protected function back(string $fallback = '/'): never
    {
        $ref = $_SERVER['HTTP_REFERER'] ?? url($fallback);
        redirect($ref);
    }

    protected function validateCsrf(Request $request): void
    {
        if (!Csrf::verify((string) $request->input('_csrf'))) {
            Response::abort(403, 'Token de segurança inválido.');
        }
    }
}
