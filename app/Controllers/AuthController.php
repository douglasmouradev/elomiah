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
use App\Models\RecuperacaoSenha;
use App\Models\Usuario;
use App\Support\ContaMail;

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

    public function recuperarForm(Request $request, array $params = []): never
    {
        if (Auth::check()) {
            redirect(Auth::isAdmin() ? '/admin' : '/conta');
        }
        $this->view('auth/recuperar', [
            'title' => 'Recuperar senha — Elomiah',
        ]);
    }

    public function recuperar(Request $request, array $params = []): never
    {
        if (RateLimiter::tooMany('recuperar')) {
            Session::setFlash('error', 'Muitos pedidos. Aguarde um pouco.');
            redirect('/recuperar-senha');
        }

        $email = strtolower(trim((string) $request->input('email')));
        RateLimiter::hit('recuperar');

        $usuario = filter_var($email, FILTER_VALIDATE_EMAIL) ? Usuario::firstWhere('email', $email) : null;
        if ($usuario && ($usuario['status'] ?? '') === 'ativo') {
            $token = RecuperacaoSenha::emitir($email);
            ContaMail::recuperar($email, $token);
        }

        Session::setFlash('success', 'Se este e-mail tiver uma conta, enviamos o caminho para uma senha nova.');
        redirect('/entrar');
    }

    public function redefinirForm(Request $request, array $params = []): never
    {
        $token = (string) ($params['token'] ?? '');
        if (!RecuperacaoSenha::encontrar($token)) {
            Session::setFlash('error', 'Este link expirou. Peça outro em Recuperar senha.');
            redirect('/recuperar-senha');
        }
        $this->view('auth/redefinir', [
            'title' => 'Nova senha — Elomiah',
            'token' => $token,
        ]);
    }

    public function redefinir(Request $request, array $params = []): never
    {
        $token = (string) ($params['token'] ?? '');
        $row = RecuperacaoSenha::encontrar($token);
        if (!$row) {
            Session::setFlash('error', 'Este link expirou. Peça outro em Recuperar senha.');
            redirect('/recuperar-senha');
        }

        $senha = (string) $request->input('senha');
        $conf = (string) $request->input('senha_confirmation');
        if (mb_strlen($senha) < 8 || $senha !== $conf) {
            Session::setFlash('error', 'A senha precisa ter ao menos 8 caracteres e coincidir com a confirmação.');
            redirect('/recuperar-senha/' . $token);
        }

        $usuario = Usuario::firstWhere('email', (string) $row['email']);
        if (!$usuario) {
            Session::setFlash('error', 'Não foi possível atualizar esta conta.');
            redirect('/recuperar-senha');
        }

        Usuario::updateById((int) $usuario['id'], [
            'senha_hash' => password_hash($senha, PASSWORD_DEFAULT),
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ]);
        RecuperacaoSenha::gastar((int) $row['id']);
        Session::setFlash('success', 'Senha atualizada. Entre com o e-mail e a senha nova.');
        redirect('/entrar');
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
