<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Models\Depoimento;

final class DepoimentoController extends Controller
{
    public function index(Request $request, array $params = []): never
    {
        $this->view('admin/depoimentos/index', [
            'title' => 'Depoimentos — Ateliê',
            'depoimentos' => Depoimento::all('id DESC'),
        ], 'layouts/admin');
    }

    public function update(Request $request, array $params = []): never
    {
        $id = (int) ($params['id'] ?? 0);
        $status = (string) $request->input('status');
        if (in_array($status, ['aprovado', 'reprovado', 'pendente'], true) && Depoimento::find($id)) {
            Depoimento::updateById($id, ['status' => $status]);
            Auth::log('Moderou depoimento', 'depoimentos', $id, null, $status);
            Session::setFlash('success', 'Depoimento atualizado.');
        }
        redirect('/admin/depoimentos');
    }
}
