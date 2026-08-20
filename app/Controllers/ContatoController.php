<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\RateLimiter;
use App\Core\Request;
use App\Core\Session;
use App\Core\Validator;
use App\Models\MensagemContato;

final class ContatoController extends Controller
{
    public function index(Request $request, array $params = []): never
    {
        $this->view('contato/index', [
            'title' => 'Contato — Elomiah',
        ]);
    }

    public function store(Request $request, array $params = []): never
    {
        if (RateLimiter::tooMany('contato')) {
            Session::setFlash('error', 'Muitas mensagens em pouco tempo. Tente novamente em instantes.');
            redirect('/contato');
        }

        $data = Validator::sanitize($request->all());
        $errors = Validator::make($data, [
            'nome' => 'required|min:2|max:120',
            'email' => 'required|email|max:180',
            'mensagem' => 'required|min:10|max:2000',
        ]);

        if ($errors) {
            Session::set('_old', $data);
            Session::setFlash('error', implode(' ', $errors));
            redirect('/contato');
        }

        MensagemContato::create([
            'nome' => $data['nome'],
            'email' => $data['email'],
            'telefone' => $data['telefone'] ?? null,
            'assunto' => $data['assunto'] ?? 'Contato pelo site',
            'mensagem' => $data['mensagem'],
        ]);

        \App\Models\Notificacao::criar(
            'contato',
            'Mensagem de ' . $data['nome'],
            mb_substr((string) ($data['assunto'] ?? 'Contato pelo site'), 0, 80),
            '/admin/contato'
        );
        \App\Support\ContatoMail::aviso([
            'nome' => $data['nome'],
            'email' => $data['email'],
            'assunto' => $data['assunto'] ?? 'Contato pelo site',
            'mensagem' => $data['mensagem'],
        ]);

        RateLimiter::hit('contato');
        Session::setFlash('success', 'Recebemos sua mensagem. Respondemos com a mesma calma com que formulamos.');
        redirect('/contato');
    }
}
