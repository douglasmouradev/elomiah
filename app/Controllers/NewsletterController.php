<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\RateLimiter;
use App\Core\Request;
use App\Core\Session;
use App\Models\ConsentimentoLgpd;
use App\Models\Newsletter;

final class NewsletterController extends Controller
{
    public function store(Request $request, array $params = []): never
    {
        $email = mb_strtolower(trim((string) $request->input('email', '')));
        $aceite = (string) $request->input('aceite', '') === '1';

        if ((string) $request->input('site', '') !== '') {
            $this->responder($request, true, 'Pronto. Você vai receber as novidades da Elomiah.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 180) {
            $this->responder($request, false, 'Confira o e-mail.');
        }
        if (!$aceite) {
            $this->responder($request, false, 'Marque o aceite para receber os e-mails.');
        }
        if (RateLimiter::tooMany('newsletter')) {
            $this->responder($request, false, 'Muitas tentativas. Tente mais tarde.');
        }
        RateLimiter::hit('newsletter');

        Newsletter::inscrever($email, client_ip());
        ConsentimentoLgpd::create([
            'usuario_id' => \App\Core\Auth::id(),
            'email' => $email,
            'tipo' => 'newsletter',
            'aceito' => 1,
            'ip' => client_ip(),
            'user_agent' => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
        ]);

        $this->responder($request, true, 'Pronto. Você vai receber as novidades da Elomiah.');
    }

    private function responder(Request $request, bool $ok, string $mensagem): never
    {
        if ($request->isAjax()) {
            $this->json(['ok' => $ok, 'message' => $mensagem], $ok ? 200 : 422);
        }
        Session::setFlash('newsletter', $mensagem);
        $caminho = (string) (parse_url((string) ($_SERVER['HTTP_REFERER'] ?? ''), PHP_URL_PATH) ?: '/');
        redirect('/' . ltrim($caminho, '/') . '#newsletter');
    }
}
