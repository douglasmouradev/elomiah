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
use App\Models\SolicitacaoLgpd;

final class LgpdController extends Controller
{
    public function privacidade(Request $request, array $params = []): never
    {
        $this->view('lgpd/privacidade', ['title' => 'Política de Privacidade — Elomiah']);
    }

    public function termos(Request $request, array $params = []): never
    {
        $this->view('lgpd/termos', ['title' => 'Termos de Uso — Elomiah']);
    }

    public function meusDados(Request $request, array $params = []): never
    {
        $this->view('lgpd/meus-dados', [
            'title' => 'Seus dados — Elomiah',
            'user' => Auth::user(),
        ]);
    }

    public function solicitar(Request $request, array $params = []): never
    {
        if (RateLimiter::tooMany('lgpd')) {
            Session::setFlash('error', 'Aguarde antes de enviar outra solicitação.');
            redirect('/meus-dados');
        }

        $data = Validator::sanitize($request->all());
        $errors = Validator::make($data, [
            'nome' => 'required|min:3',
            'email' => 'required|email',
            'tipo' => 'required|in:exportar,excluir',
        ]);

        if ($errors) {
            Session::setFlash('error', implode(' ', $errors));
            redirect('/meus-dados');
        }

        SolicitacaoLgpd::create([
            'nome' => $data['nome'],
            'email' => strtolower((string) $data['email']),
            'tipo' => $data['tipo'],
            'mensagem' => $data['mensagem'] ?? null,
            'status' => 'pendente',
            'ip' => client_ip(),
        ]);

        \App\Models\Notificacao::criar(
            'lgpd',
            'Pedido LGPD · ' . (($data['tipo'] ?? '') === 'excluir' ? 'excluir' : 'exportar'),
            $data['nome'] . ' · ' . $data['email'],
            '/admin/lgpd'
        );

        RateLimiter::hit('lgpd');
        Session::setFlash('success', 'Solicitação registrada. O titular dos dados (Geo / Elomiah) responderá em até 15 dias.');
        redirect('/meus-dados');
    }

    public function cookies(Request $request, array $params = []): never
    {
        $escolha = (string) $request->input('escolha', 'essenciais');
        if (!in_array($escolha, ['essenciais', 'todos', 'recusar'], true)) {
            $escolha = 'essenciais';
        }

        setcookie('elomiah_cookies', $escolha, [
            'expires' => time() + 60 * 60 * 24 * 365,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Strict',
            'secure' => (bool) (config('app')['session_secure'] ?? false),
        ]);

        ConsentimentoLgpd::create([
            'usuario_id' => Auth::id(),
            'email' => Auth::user()['email'] ?? null,
            'tipo' => 'cookies_' . $escolha,
            'aceito' => $escolha !== 'recusar' ? 1 : 0,
            'ip' => client_ip(),
            'user_agent' => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
        ]);

        if ($request->isAjax()) {
            $this->json(['ok' => true]);
        }
        $this->back('/');
    }
}
