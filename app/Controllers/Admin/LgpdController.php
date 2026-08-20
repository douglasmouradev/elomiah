<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\SolicitacaoLgpd;

final class LgpdController extends Controller
{
    public function index(Request $request, array $params = []): never
    {
        $this->view('admin/lgpd/index', [
            'title' => 'Dados (LGPD) — Ateliê',
            'solicitacoes' => SolicitacaoLgpd::all('id DESC'),
        ], 'layouts/admin');
    }

    public function atender(Request $request, array $params = []): never
    {
        $id = (int) ($params['id'] ?? 0);
        $row = SolicitacaoLgpd::find($id);
        if (!$row) {
            Response::abort(404, 'Solicitação não encontrada.');
        }
        SolicitacaoLgpd::updateById($id, ['status' => 'atendida']);
        Auth::log('Marcou solicitação LGPD como atendida', 'solicitacoes_lgpd', $id);
        Session::setFlash('success', 'Solicitação marcada como atendida.');
        redirect('/admin/lgpd');
    }
}
