<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Notificacao;
use App\Models\Pedido;
use App\Support\PedidoMail;

final class PedidoController extends Controller
{
    public function index(Request $request, array $params = []): never
    {
        Pedido::garantirColunas();
        $status = (string) $request->input('status', '');
        $pedidos = $status && in_array($status, Pedido::statuses(), true)
            ? Pedido::where('status', $status, 'id DESC')
            : Pedido::all('id DESC');

        $this->view('admin/pedidos/index', [
            'title' => 'Pedidos — Ateliê',
            'pedidos' => $pedidos,
            'status' => $status,
        ], 'layouts/admin');
    }

    public function show(Request $request, array $params = []): never
    {
        Pedido::garantirColunas();
        $pedido = Pedido::withItems((int) ($params['id'] ?? 0));
        if (!$pedido) {
            Response::abort(404, 'Pedido não encontrado.');
        }
        Notificacao::marcarPedido((int) $pedido['id']);
        $this->view('admin/pedidos/show', [
            'title' => 'Pedido ' . $pedido['codigo'],
            'pedido' => $pedido,
        ], 'layouts/admin');
    }

    public function status(Request $request, array $params = []): never
    {
        $id = (int) ($params['id'] ?? 0);
        $pedido = Pedido::find($id);
        $status = (string) $request->input('status');
        if (!$pedido) {
            Session::setFlash('error', 'Pedido não encontrado.');
            redirect('/admin/pedidos');
        }

        $ok = Pedido::mudarStatus($id, $status, [
            'codigo_rastreio' => (string) $request->input('codigo_rastreio', ''),
            'transportadora' => (string) $request->input('transportadora', ''),
        ]);

        if ($ok) {
            Auth::log('Atualizou status do pedido', 'pedidos', $id, $pedido['status'], $status);
            Session::setFlash('success', 'Pedido ' . $pedido['codigo'] . ': ' . pedido_status_rotulo($status) . '.');
            $antes = (string) ($pedido['status'] ?? '');
            if ($status === 'pago' && $antes !== 'pago') {
                PedidoMail::pago(['id' => $id]);
            }
            if ($status === 'enviado' && $antes !== 'enviado') {
                PedidoMail::enviado(['id' => $id]);
            }
        } else {
            Session::setFlash('error', 'Não foi possível atualizar o status.');
        }

        $voltar = (string) $request->input('voltar', '');
        redirect($voltar === 'lista' ? '/admin/pedidos' : '/admin/pedidos/' . $id);
    }
}
