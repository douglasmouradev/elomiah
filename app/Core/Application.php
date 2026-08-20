<?php

declare(strict_types=1);

namespace App\Core;

use App\Middleware\GuestTracking;
use Throwable;

final class Application
{
    public function run(): void
    {
        Session::start();

        $request = new Request();

        $csrfExempt = str_starts_with($request->path(), '/webhooks/');
        if (!$csrfExempt && in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            $token = (string) $request->input('_csrf', $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
            if (!Csrf::verify($token)) {
                if ($request->isAjax()) {
                    Response::json(['ok' => false, 'message' => 'Sessão expirada. Recarregue a página.'], 419);
                }
                Session::setFlash('error', 'A sessão expirou. Envie o formulário novamente.');
                redirect($_SERVER['HTTP_REFERER'] ?? '/');
            }
        }

        try {
            (new GuestTracking())->handle($request);
            $this->expirarPedidosPendentes();
            $router = new Router();
            $register = require CONFIG_PATH . DIRECTORY_SEPARATOR . 'routes.php';
            $register($router);
            $router->dispatch($request);
        } catch (Throwable $e) {
            $this->handleException($e);
        }
    }

    private function expirarPedidosPendentes(): void
    {
        $lock = STORAGE_PATH . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . '.expire-pedidos';
        if (is_file($lock) && filemtime($lock) > time() - 600) {
            return;
        }
        @touch($lock);
        try {
            \App\Models\Pedido::expirarPendentes(24);
        } catch (Throwable) {
        }
    }

    private function handleException(Throwable $e): void
    {
        $log = STORAGE_PATH . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'app.log';
        $line = sprintf("[%s] %s in %s:%d\n", date('c'), $e->getMessage(), $e->getFile(), $e->getLine());
        @file_put_contents($log, $line, FILE_APPEND);

        if (config('app')['debug'] ?? false) {
            Response::html('<pre style="padding:2rem;font-family:monospace">' . e($e->getMessage() . "\n\n" . $e->getTraceAsString()) . '</pre>', 500);
        }

        Response::abort(500, 'Ocorreu um erro inesperado. Tente novamente em instantes.');
    }
}
