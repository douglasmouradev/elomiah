<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Cart;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Models\Produto;

final class CarrinhoController extends Controller
{
    public function index(Request $request, array $params = []): never
    {
        $carrinho = Cart::detailed();
        $frete = frete_do_carrinho($carrinho);
        $this->view('carrinho/index', [
            'title' => 'Sacola — Elomiah',
            'carrinho' => $carrinho,
            'freteInfo' => $frete,
            'freteGratis' => frete_gratis_progresso($carrinho),
            'estimado' => $carrinho['total'] + $frete['valor'],
        ]);
    }

    public function resumo(Request $request, array $params = []): never
    {
        $this->json($this->estado());
    }

    public function add(Request $request, array $params = []): never
    {
        $id = (int) $request->input('produto_id', 0);
        $qty = (int) $request->input('quantidade', 1);
        $produto = Produto::find($id);
        if (!$produto || $produto['status'] !== 'ativo') {
            $this->falhar($request, 'Este item não está disponível.', '/loja');
        }

        if ($produto['compra_tipo'] !== 'carrinho') {
            $url = match ($produto['compra_tipo']) {
                'shopee' => shopee_url(),
                'amazon' => $produto['url_amazon'],
                'mercadolivre' => $produto['url_mercadolivre'],
                default => null,
            };
            if ($url) {
                if ($request->isAjax()) {
                    $this->json(['ok' => false, 'redirect' => $url]);
                }
                redirect($url);
            }
        }

        $teto = Cart::teto($id);
        if ($teto < 1) {
            $this->falhar($request, $produto['nome'] . ' esgotou. Fale com o ateliê pelo WhatsApp para saber quando volta.', '/loja');
        }
        if (Cart::qty($id) + $qty > $teto) {
            $this->falhar($request, 'Restam só ' . $teto . ' unidades de ' . $produto['nome'] . '. A sacola já tem ' . Cart::qty($id) . '.', '/carrinho');
        }

        Cart::add($id, $qty);
        $mensagem = $produto['nome'] . ' foi para a sacola.';
        if ($request->isAjax()) {
            $this->json(['ok' => true, 'message' => $mensagem, 'adicionado' => $id] + $this->estado());
        }
        Session::setFlash('success', $mensagem);
        redirect('/carrinho');
    }

    public function update(Request $request, array $params = []): never
    {
        Cart::update((int) $request->input('produto_id'), (int) $request->input('quantidade', 1));
        if ($request->isAjax()) {
            $this->json(['ok' => true] + $this->estado());
        }
        redirect('/carrinho');
    }

    public function remove(Request $request, array $params = []): never
    {
        Cart::remove((int) $request->input('produto_id'));
        if ($request->isAjax()) {
            $this->json(['ok' => true] + $this->estado());
        }
        redirect('/carrinho');
    }

    private function falhar(Request $request, string $mensagem, string $volta): never
    {
        if ($request->isAjax()) {
            $this->json(['ok' => false, 'message' => $mensagem] + $this->estado(), 422);
        }
        Session::setFlash('error', $mensagem);
        $this->back($volta);
    }

    private function estado(): array
    {
        $carrinho = Cart::detailed();
        $frete = frete_do_carrinho($carrinho);
        $itens = [];
        foreach ($carrinho['items'] as $item) {
            $p = $item['produto'];
            $itens[] = [
                'id' => (int) $p['id'],
                'nome' => (string) $p['nome'],
                'detalhe' => trim((string) ($p['aroma'] ?? '') . ', ' . (string) ($p['volume'] ?? '120 ml'), ', '),
                'cor' => (string) ($p['cor_destaque'] ?? '#173A2C'),
                'imagem' => asset((string) ($p['imagem'] ?? 'images/frasco-despertar.webp')),
                'url' => url('/produto/' . $p['slug']),
                'qty' => (int) $item['qty'],
                'teto' => min(20, Cart::teto((int) $p['id'])),
                'subtotal' => money($item['subtotal']),
            ];
        }

        return [
            'count' => Cart::count(),
            'itens' => $itens,
            'pecas' => money($carrinho['total']),
            'frete' => ['nome' => $frete['nome'], 'valor' => money($frete['valor']), 'prazo' => $frete['prazo']],
            'total' => money($carrinho['total'] + $frete['valor']),
            'freteGratis' => (static function (array $p): array {
                $p['falta'] = money($p['falta']);
                return $p;
            })(frete_gratis_progresso($carrinho)),
            'logado' => \App\Core\Auth::check(),
        ];
    }
}
