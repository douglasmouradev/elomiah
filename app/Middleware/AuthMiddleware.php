<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Session;

final class AuthMiddleware
{
    public function handle(Request $request): void
    {
        if (Auth::check()) {
            return;
        }

        Session::set('intended', $request->path());
        Session::setFlash('error', 'Entre na sua conta para acompanhar os pedidos ou concluir a compra.');
        redirect('/entrar');
    }
}
