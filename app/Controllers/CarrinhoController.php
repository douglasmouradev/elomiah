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
        $frete = \App\Models\Configuracao::frete();
        $carrinho = Cart::detailed();
        $this->view('carrinho/index', [
            'title' => 'Sacola — Elomiah',
            'carrinho' => $carrinho,
            'freteInfo' => $frete,
            'estimado' => $carrinho['total'] + $frete['valor'],
        ]);
    }

    public function add(Request $request, array $params = []): never
    {
        $id = (int) $request->input('produto_id', 0);
        $qty = (int) $request->input('quantidade', 1);
        $produto = Produto::find($id);
        if (!$produto || $produto['status'] !== 'ativo') {
            Session::setFlash('error', 'Este item não está disponível.');
            $this->back('/loja');
        }

        if ($produto['compra_tipo'] !== 'carrinho') {
            $url = match ($produto['compra_tipo']) {
                'shopee' => shopee_url(),
                'amazon' => $produto['url_amazon'],
                'mercadolivre' => $produto['url_mercadolivre'],
                default => null,
            };
            if ($url) {
                redirect($url);
            }
        }

        Cart::add($id, $qty);
        Session::setFlash('success', $produto['nome'] . ' entrou na sacola.');
        redirect('/carrinho');
    }

    public function update(Request $request, array $params = []): never
    {
        Cart::update((int) $request->input('produto_id'), (int) $request->input('quantidade', 1));
        redirect('/carrinho');
    }

    public function remove(Request $request, array $params = []): never
    {
        Cart::remove((int) $request->input('produto_id'));
        redirect('/carrinho');
    }
}
