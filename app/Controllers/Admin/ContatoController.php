<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\MensagemContato;

final class ContatoController extends Controller
{
    public function index(Request $request, array $params = []): never
    {
        $this->view('admin/contato/index', [
            'title' => 'Contato — Ateliê',
            'mensagens' => MensagemContato::all('id DESC'),
        ], 'layouts/admin');
    }

    public function lida(Request $request, array $params = []): never
    {
        $id = (int) ($params['id'] ?? 0);
        $msg = MensagemContato::find($id);
        if (!$msg) {
            Response::abort(404, 'Mensagem não encontrada.');
        }
        MensagemContato::updateById($id, ['lido' => 1]);
        Auth::log('Marcou mensagem de contato como lida', 'mensagens_contato', $id);
        Session::setFlash('success', 'Mensagem marcada como lida.');
        redirect('/admin/contato');
    }
}
