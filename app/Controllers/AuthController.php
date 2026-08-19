<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\RateLimiter;
use App\Core\Request;
use App\Core\Session;
use App\Core\Validator;
use App\Models\ConsentimentoLgpd;
use App\Models\Usuario;

final class AuthController extends Controller
{
    public function loginForm(Request $request, array $params = []): never
    {
        if (Auth::check()) {
            redirect(Auth::isAdmin() ? '/admin' : '/conta');
        }
        $this->view('auth/login', [
            'title' => 'Entrar — Elomiah',
        ]);
    }

    public function login(Request $request, array $params = []): never
    {
        if (RateLimiter::tooMany('login')) {
            Session::setFlash('error', 'Muitas tentativas. Aguarde alguns minutos.');
            redirect('/entrar');
        }

        $email = strtolower(trim((string) $request->input('email')));
        $senha = (string) $request->input('senha');

        if (RateLimiter::accountLocked($email)) {
            Session::setFlash('error', 'Esta conta está temporariamente bloqueada. Tente em 15 minutos.');
            redirect('/entrar');
        }

        if (!Auth::attempt($email, $senha)) {
            RateLimiter::hit('login');
            RateLimiter::registerFailure($email);
            Session::setFlash('error', 'E-mail ou senha não conferem.');
            redirect('/entrar');
        }

        $user = Auth::user();
        if ($user) {
            RateLimiter::clearFailures((int) $user['id']);
        }

        redirect($this->depoisDeEntrar($user));
    }

    public function registerForm(Request $request, array $params = []): never
    {
        if (Auth::check()) {
            redirect('/conta');
        }
        $this->view('auth/cadastro', [
            'title' => 'Cadastro — Elomiah',
        ]);
    }

    public function register(Request $request, array $params = []): never
    {
        $data = Validator::sanitize($request->all());
        $errors = Validator::make($data, [
            'nome' => 'required|min:3|max:120',
            'email' => 'required|email',
            'senha' => 'required|min:8',
            'senha_confirmation' => 'required',
            'lgpd' => 'required',
        ]);

        if (($data['senha'] ?? '') !== ($data['senha_confirmation'] ?? '')) {
            $errors['senha'] = 'A confirmação da senha não confere.';
        }

        if (Usuario::firstWhere('email', strtolower((string) $data['email']))) {
            $errors['email'] = 'Este e-mail já possui cadastro.';
        }

        if ($errors) {
            Session::set('_old', $data);
            Session::setFlash('error', implode(' ', $errors));
            redirect('/cadastro');
        }

        $id = Usuario::create([
            'nome' => $data['nome'],
            'email' => strtolower((string) $data['email']),
            'senha_hash' => password_hash((string) $data['senha'], PASSWORD_DEFAULT),
            'telefone' => $data['telefone'] ?? null,
            'role' => 'cliente',
            'status' => 'ativo',
        ]);

        ConsentimentoLgpd::create([
            'usuario_id' => $id,
            'email' => strtolower((string) $data['email']),
            'tipo' => 'privacidade_cadastro',
            'aceito' => 1,
            'ip' => client_ip(),
            'user_agent' => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
        ]);

        Auth::loginById($id);
        Session::setFlash('success', 'Bem-vinda ao refúgio. Seus pedidos e o rastreio ficam nesta conta.');
        redirect($this->depoisDeEntrar(Auth::user()));
    }

    public function logout(Request $request, array $params = []): never
    {
        Auth::logout();
        redirect('/');
    }

    private function depoisDeEntrar(?array $user): string
    {
        $intended = (string) Session::get('intended', '');
        Session::forget('intended');
        if (
            $intended !== ''
            && str_starts_with($intended, '/')
            && !str_starts_with($intended, '//')
            && !str_starts_with($intended, '/admin')
        ) {
            return $intended;
        }

        return ($user['role'] ?? '') === 'admin' ? '/admin' : '/conta';
    }
}
