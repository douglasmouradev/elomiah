<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Pedido;
use App\Support\MercadoPago;

final class PagamentoController extends Controller
{
    public function retorno(Request $request, array $params = []): never
    {
        $paymentId = (string) $request->input('payment_id', $request->input('collection_id', ''));
        if ($paymentId !== '' && $paymentId !== 'null') {
            MercadoPago::sincronizarPagamento($paymentId);
        }

        $codigo = (string) $request->input('external_reference', Session::get('ultimo_pedido', ''));
        $statusMp = (string) $request->input('status', $request->input('collection_status', ''));

        if ($statusMp === 'rejected' || $statusMp === 'cancelled') {
            Session::setFlash('error', 'O pagamento não foi concluído. Você pode tentar de novo nesta página.');
        } elseif ($statusMp === 'pending') {
            Session::setFlash('success', 'Pagamento em processamento. Assim que o Mercado Pago confirmar, o pedido muda para pago.');
        }

        if ($codigo === '') {
            redirect('/');
        }
        redirect('/pedido/' . $codigo);
    }

    public function webhook(Request $request, array $params = []): never
    {
        $raw = file_get_contents('php://input') ?: '';
        $json = json_decode($raw, true);
        if (!is_array($json)) {
            $json = [];
        }

        $type = (string) ($json['type'] ?? $json['action'] ?? $request->input('type', $request->input('topic', '')));
        $id = (string) ($json['data']['id'] ?? $request->input('data_id', $request->input('id', '')));

        if ($id !== '' && (str_contains($type, 'payment') || $type === 'payment')) {
            MercadoPago::sincronizarPagamento($id);
        }

        Response::json(['ok' => true]);
    }
}
