<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Session;

final class AdminMiddleware
{
    public function handle(Request $request): void
    {
        if (!Auth::isAdmin()) {
            Session::setFlash('error', 'Acesso restrito ao ateliê.');
            redirect('/admin/login');
        }
    }
}
