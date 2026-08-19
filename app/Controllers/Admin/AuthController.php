<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\RateLimiter;
use App\Core\Request;
use App\Core\Session;

final class AuthController extends Controller
{
    public function form(Request $request, array $params = []): never
    {
        if (Auth::isAdmin()) {
            redirect('/admin');
        }
        $this->view('admin/login', [
            'title' => 'Ateliê — Elomiah',
        ], 'layouts/admin-guest');
    }

    public function login(Request $request, array $params = []): never
    {
        if (RateLimiter::tooMany('login')) {
            Session::setFlash('error', 'Muitas tentativas. Aguarde 15 minutos.');
            redirect('/admin/login');
        }

        $email = strtolower(trim((string) $request->input('email')));
        $senha = (string) $request->input('senha');

        if (RateLimiter::accountLocked($email)) {
            Session::setFlash('error', 'Conta bloqueada temporariamente.');
            redirect('/admin/login');
        }

        if (!Auth::attempt($email, $senha) || !Auth::isAdmin()) {
            RateLimiter::hit('login');
            RateLimiter::registerFailure($email);
            Auth::logout();
            Session::setFlash('error', 'Credenciais inválidas para o ateliê.');
            redirect('/admin/login');
        }

        RateLimiter::clearFailures((int) Auth::id());
        Auth::log('Login no painel', 'sessao');
        redirect('/admin');
    }

    public function logout(Request $request, array $params = []): never
    {
        Auth::log('Saída do painel', 'sessao');
        Auth::logout();
        redirect('/admin/login');
    }
}
