<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Cart;
use App\Core\Controller;
use App\Core\RateLimiter;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Models\ConsentimentoLgpd;
use App\Models\Endereco;
use App\Models\ItemPedido;
use App\Models\Notificacao;
use App\Models\Pedido;
use App\Models\Produto;
use App\Support\MercadoPago;
use App\Support\PedidoMail;
use App\Support\Pix;
use RuntimeException;

final class CheckoutController extends Controller
{
    public function index(Request $request, array $params = []): never
    {
        $carrinho = Cart::detailed();
        if ($carrinho['items'] === []) {
            Session::setFlash('error', 'Sua sacola está vazia.');
            redirect('/loja');
        }

        $user = Auth::user();
        $requerEnvio = carrinho_requer_envio($carrinho);
        $endereco = $requerEnvio ? Endereco::ultimoDoCliente((int) Auth::id()) : null;
        $frete = frete_do_carrinho($carrinho);
        $total = $carrinho['total'] + $frete['valor'];
        $pixPronto = Pix::configurado() || MercadoPago::configurado();

        $this->view('checkout/index', [
            'title' => 'Checkout — Elomiah',
            'carrinho' => $carrinho,
            'frete' => $frete['valor'],
            'freteInfo' => $frete,
            'total' => $total,
            'user' => $user,
            'endereco' => $endereco,
            'requerEnvio' => $requerEnvio,
            'mpPronto' => MercadoPago::configurado(),
            'pixPronto' => Pix::configurado(),
            'descontoPix' => $pixPronto ? valor_desconto_pix($total) : 0.0,
            'pctPix' => $pixPronto ? desconto_pix() : 0.0,
            'brinde' => brinde_progresso($carrinho),
        ]);
    }

    public function store(Request $request, array $params = []): never
    {
        if (RateLimiter::tooMany('checkout')) {
            Session::setFlash('error', 'Muitas tentativas. Aguarde um momento.');
            redirect('/checkout');
        }

        $carrinho = Cart::detailed();
        if ($carrinho['items'] === []) {
            redirect('/loja');
        }

        foreach ($carrinho['items'] as $item) {
            $produto = $item['produto'];
            if (produto_digital($produto)) {
                continue;
            }
            $atual = \App\Models\Produto::find((int) $produto['id']);
            if (!$atual || (int) ($atual['estoque'] ?? 0) < (int) $item['qty']) {
                Session::setFlash('error', ($produto['nome'] ?? 'Um item') . ' não tem estoque suficiente. Ajuste a sacola.');
                redirect('/carrinho');
            }
        }

        $data = Validator::sanitize($request->all());
        $requerEnvio = carrinho_requer_envio($carrinho);
        $regras = [
            'nome' => 'required|min:3|max:120',
            'email' => 'required|email',
            'telefone' => 'required|min:10|max:20',
            'lgpd' => 'required',
            'metodo_pagamento' => 'required|in:pix,cartao',
        ];
        if ($requerEnvio) {
            $regras = array_merge($regras, [
                'cep' => 'required|cep',
                'logradouro' => 'required|max:180',
                'numero' => 'required|max:20',
                'bairro' => 'required|max:120',
                'cidade' => 'required|max:120',
                'estado' => 'required|max:2',
            ]);
        }
        $errors = Validator::make($data, $regras);

        $metodo = (string) ($data['metodo_pagamento'] ?? 'pix');

        if ($errors) {
            Session::set('_old', $data);
            Session::setFlash('error', implode(' ', $errors));
            redirect('/checkout');
        }

        if ($metodo === 'cartao' && !MercadoPago::configurado()) {
            Session::set('_old', $data);
            Session::setFlash('error', 'O cartão confirma nesta página pelo Mercado Pago. O Pix já está pronto — escolha Pix para ver o QR.');
            redirect('/checkout');
        }

        if ($metodo === 'pix' && !MercadoPago::configurado() && !Pix::configurado()) {
            Session::set('_old', $data);
            Session::setFlash('error', 'O pagamento Pix ainda não tem chave neste ateliê.');
            redirect('/checkout');
        }

        $enderecoId = null;
        if ($requerEnvio) {
            $cep = preg_replace('/\D+/', '', (string) $data['cep']) ?? '';
            $enderecoId = Endereco::create([
                'usuario_id' => Auth::id(),
                'cep' => $cep,
                'logradouro' => $data['logradouro'],
                'numero' => $data['numero'],
                'complemento' => $data['complemento'] ?? null,
                'bairro' => $data['bairro'],
                'cidade' => $data['cidade'],
                'estado' => strtoupper((string) $data['estado']),
            ]);
        }

        $frete = frete_do_carrinho($carrinho)['valor'];
        $desconto = $metodo === 'pix' ? valor_desconto_pix($carrinho['total'] + $frete) : 0.0;
        $observacoes = trim((string) ($data['observacoes'] ?? ''));
        $brinde = brinde_progresso($carrinho);
        if ($brinde['atingido']) {
            $observacoes = trim($observacoes . "\nBrinde: " . $brinde['texto']);
        }
        $codigo = 'ELO-' . strtoupper(bin2hex(random_bytes(4)));

        $pedidoId = Pedido::create([
            'usuario_id' => Auth::id(),
            'endereco_id' => $enderecoId,
            'codigo' => $codigo,
            'status' => 'pendente',
            'subtotal' => $carrinho['total'],
            'frete' => $frete,
            'desconto' => $desconto,
            'total' => $carrinho['total'] + $frete - $desconto,
            'metodo_pagamento' => $metodo,
            'observacoes' => $observacoes !== '' ? $observacoes : null,
            'nome_cliente' => $data['nome'],
            'email_cliente' => strtolower((string) $data['email']),
            'telefone_cliente' => preg_replace('/\D+/', '', (string) $data['telefone']),
        ]);

        foreach ($carrinho['items'] as $item) {
            ItemPedido::create([
                'pedido_id' => $pedidoId,
                'produto_id' => $item['produto']['id'],
                'nome_produto' => $item['produto']['nome'],
                'quantidade' => $item['qty'],
                'preco_unitario' => $item['preco'],
                'subtotal' => $item['subtotal'],
            ]);
            if (!produto_digital($item['produto'])) {
                $estoque = max(0, (int) $item['produto']['estoque'] - $item['qty']);
                Produto::updateById((int) $item['produto']['id'], ['estoque' => $estoque]);
            }
        }

        ConsentimentoLgpd::create([
            'usuario_id' => Auth::id(),
            'email' => strtolower((string) $data['email']),
            'tipo' => 'privacidade_checkout',
            'aceito' => 1,
            'ip' => client_ip(),
            'user_agent' => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
        ]);

        RateLimiter::hit('checkout');
        Session::set('ultimo_pedido', $codigo);

        $pedidoRef = [
            'codigo' => $codigo,
            'nome_cliente' => $data['nome'],
            'email_cliente' => strtolower((string) $data['email']),
        ];

        $link = '';
        $totalPedido = $carrinho['total'] + $frete - $desconto;
        try {
            if ($metodo === 'pix') {
                $this->salvarPix($pedidoId, $pedidoRef, $codigo, $totalPedido);
            } elseif (MercadoPago::cartaoNoSite()) {
                // Cartão preenchido nesta página, no pedido.
            } else {
                $preferencia = MercadoPago::criarPreferencia($pedidoRef, $carrinho['items'], $frete, $metodo);
                $link = MercadoPago::linkPagamento($preferencia);
                if ($link === '') {
                    throw new RuntimeException('O Mercado Pago não devolveu o link de pagamento.');
                }
                Pedido::updateById($pedidoId, [
                    'mp_preference_id' => (string) ($preferencia['id'] ?? ''),
                    'mp_init_point' => $link,
                ]);
            }
        } catch (RuntimeException $e) {
            foreach ($carrinho['items'] as $item) {
                Produto::updateById((int) $item['produto']['id'], ['estoque' => (int) $item['produto']['estoque']]);
            }
            Pedido::updateById($pedidoId, ['status' => 'cancelado']);
            Session::set('_old', $data);
            Session::setFlash('error', 'Não foi possível abrir o pagamento: ' . $e->getMessage());
            redirect('/checkout');
        }

        Cart::clear();
        Session::forget('_old');

        Notificacao::pedidoNovo(
            $pedidoId,
            $codigo,
            (string) $data['nome'],
            (float) $totalPedido
        );
        PedidoMail::recebido(['id' => $pedidoId]);

        if ($metodo === 'pix' || $link === '') {
            redirect('/pedido/' . $codigo);
        }
        redirect($link);
    }

    public function pagarCartao(Request $request, array $params = []): never
    {
        if (RateLimiter::tooMany('pagar_cartao')) {
            Response::json(['ok' => false, 'message' => 'Muitas tentativas. Aguarde um pouco.'], 429);
        }

        $codigo = (string) ($params['codigo'] ?? '');
        $pedido = Pedido::firstWhere('codigo', $codigo);
        if (!$pedido) {
            Response::json(['ok' => false, 'message' => 'Pedido não encontrado.'], 404);
        }

        if (!$this->podeVerPedido($pedido, $codigo)) {
            Response::json(['ok' => false, 'message' => 'Entre na conta para pagar este pedido.'], 403);
        }

        if (($pedido['status'] ?? '') !== 'pendente' || ($pedido['metodo_pagamento'] ?? '') !== 'cartao') {
            Response::json(['ok' => false, 'message' => 'Este pedido não aguarda cartão.'], 422);
        }

        RateLimiter::hit('pagar_cartao');

        $raw = file_get_contents('php://input') ?: '';
        $card = json_decode($raw, true);
        if (!is_array($card) || $card === []) {
            $card = $request->all();
        }

        try {
            $payment = MercadoPago::criarPagamentoCartao($pedido, $card);
            $paymentId = (string) ($payment['id'] ?? '');
            if ($paymentId !== '') {
                MercadoPago::sincronizarPagamento($paymentId);
            }
        } catch (RuntimeException $e) {
            Response::json(['ok' => false, 'message' => $e->getMessage()], 422);
        }

        $pedido = Pedido::firstWhere('codigo', $codigo) ?? $pedido;
        $status = (string) ($pedido['status'] ?? 'pendente');
        $mpStatus = (string) ($pedido['mp_status'] ?? '');
        if ($status !== 'pago') {
            $msg = match ($mpStatus) {
                'rejected', 'cancelled' => 'O cartão não autorizou. Tente outro ou pague no Pix.',
                default => 'O cartão ainda não confirmou. Tente de novo.',
            };
            Response::json(['ok' => false, 'status' => $status, 'message' => $msg], 422);
        }

        Response::json(['ok' => true, 'status' => $status, 'pago' => true]);
    }

    public function nota(Request $request, array $params = []): never
    {
        $codigo = (string) ($params['codigo'] ?? '');
        $pedido = Pedido::firstWhere('codigo', $codigo);
        if (!$pedido) {
            Response::abort(404, 'Pedido não encontrado.');
        }

        $pedido = Pedido::withItems((int) $pedido['id']) ?? $pedido;
        if (!$this->podeVerPedido($pedido, $codigo)) {
            Session::set('intended', '/pedido/' . $codigo . '/nota');
            Session::setFlash('error', 'Entre na sua conta para ver o recibo.');
            redirect('/entrar');
        }

        if (!\App\Support\NotaCompra::liberada($pedido)) {
            Session::setFlash('error', 'O recibo sai por e-mail quando o pagamento entrar.');
            redirect('/pedido/' . $codigo);
        }

        Response::html(\App\Support\NotaCompra::html($pedido, true));
    }

    public function obrigado(Request $request, array $params = []): never
    {
        $codigo = (string) ($params['codigo'] ?? '');
        $pedido = Pedido::firstWhere('codigo', $codigo);
        if (!$pedido) {
            \App\Core\Response::abort(404, 'Pedido não encontrado.');
        }

        MercadoPago::sincronizarPedidoPendente($pedido);
        $pedido = Pedido::withItems((int) $pedido['id']) ?? $pedido;

        if (!$this->podeVerPedido($pedido, $codigo)) {
            Session::set('intended', '/conta/pedidos/' . $codigo);
            Session::setFlash('error', 'Entre na sua conta para ver este pedido.');
            redirect('/entrar');
        }

        $pixPayload = (string) ($pedido['pix_qr_code'] ?? '');
        if ($pixPayload === '' && ($pedido['metodo_pagamento'] ?? '') === 'pix' && empty($pedido['mp_payment_id']) && Pix::configurado()) {
            $pixPayload = Pix::copiaECola((string) $pedido['codigo'], (float) $pedido['total']);
        }

        $this->view('checkout/obrigado', [
            'title' => 'Pedido recebido — Elomiah',
            'pedido' => $pedido,
            'pixPayload' => $pixPayload,
            'pixChave' => Pix::chave(),
            'pixQrBase64' => (string) ($pedido['pix_qr_base64'] ?? ''),
            'mpPublicKey' => MercadoPago::chavePublica(),
            'cursoUrl' => (\App\Models\Curso::clienteTemAcesso((int) ($pedido['usuario_id'] ?? 0))
                || (in_array((string) ($pedido['status'] ?? ''), ['pago', 'enviado', 'entregue'], true) && pedido_tem_formacao($pedido)))
                ? \App\Models\Curso::urlAcesso()
                : '',
        ]);
    }

    public function status(Request $request, array $params = []): never
    {
        header('Cache-Control: no-store');
        $codigo = (string) ($params['codigo'] ?? '');
        $pedido = Pedido::firstWhere('codigo', $codigo);
        if (!$pedido || !$this->podeVerPedido($pedido, $codigo)) {
            Response::json(['ok' => false], 404);
        }

        if (RateLimiter::tooMany('pedido_status')) {
            $status = (string) ($pedido['status'] ?? 'pendente');
            Response::json([
                'ok' => true,
                'status' => $status,
                'pago' => in_array($status, ['pago', 'enviado', 'entregue'], true),
            ]);
        }

        RateLimiter::hit('pedido_status');
        MercadoPago::sincronizarPedidoPendente($pedido);
        $pedido = Pedido::firstWhere('codigo', $codigo) ?? $pedido;

        $status = (string) ($pedido['status'] ?? 'pendente');
        Response::json([
            'ok' => true,
            'status' => $status,
            'pago' => in_array($status, ['pago', 'enviado', 'entregue'], true),
        ]);
    }

    /** @param array<string, mixed> $pedido */
    private function podeVerPedido(array $pedido, string $codigo): bool
    {
        $dono = Auth::id() && (int) ($pedido['usuario_id'] ?? 0) === Auth::id();
        $recem = Session::get('ultimo_pedido') === $codigo;

        return $dono || $recem || Auth::isAdmin();
    }

    /**
     * @param array<string, mixed> $pedidoRef
     */
    private function salvarPix(int $pedidoId, array $pedidoRef, string $codigo, float $total): void
    {
        if (MercadoPago::configurado()) {
            try {
                $cobranca = MercadoPago::criarPix($pedidoRef, $total);
                $pix = MercadoPago::dadosPix($cobranca);
                if ($pix['qr_code'] !== '') {
                    Pedido::updateById($pedidoId, [
                        'mp_payment_id' => $pix['id'],
                        'mp_status' => $pix['status'],
                        'pix_qr_code' => $pix['qr_code'],
                        'pix_qr_base64' => $pix['qr_base64'] !== '' ? $pix['qr_base64'] : null,
                    ]);
                    return;
                }
            } catch (RuntimeException) {
                // Cai na chave Pix da Elomiah.
            }
        }

        if (!Pix::configurado()) {
            throw new RuntimeException('O Pix desta página ainda não tem chave.');
        }

        Pedido::updateById($pedidoId, [
            'pix_qr_code' => Pix::copiaECola($codigo, $total),
        ]);
    }
}
