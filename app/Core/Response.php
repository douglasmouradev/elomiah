<?php

declare(strict_types=1);

namespace App\Core;

final class Response
{
    public static function html(string $content, int $code = 200): never
    {
        http_response_code($code);
        header('Content-Type: text/html; charset=UTF-8');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        echo $content;
        exit;
    }

    public static function json(array $data, int $code = 200): never
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=UTF-8');
        header('X-Content-Type-Options: nosniff');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function abort(int $code, string $message = ''): never
    {
        $titles = [
            403 => 'Acesso negado',
            404 => 'Página não encontrada',
            429 => 'Muitas tentativas',
            500 => 'Algo saiu do esperado',
        ];
        $title = $titles[$code] ?? 'Erro';
        $view = View::render('errors/generic', [
            'code' => $code,
            'title' => $title,
            'message' => $message !== '' ? $message : $title,
        ], 'layouts/public');
        self::html($view, $code);
    }
}
