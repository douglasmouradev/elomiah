<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\Produto;

final class Cart
{
    public static function items(): array
    {
        return Session::get('cart', []);
    }

    public static function add(int $produtoId, int $qty = 1): void
    {
        $qty = max(1, min(20, $qty));
        $cart = self::items();
        $cart[$produtoId] = ($cart[$produtoId] ?? 0) + $qty;
        Session::set('cart', $cart);
    }

    public static function update(int $produtoId, int $qty): void
    {
        $cart = self::items();
        if ($qty <= 0) {
            unset($cart[$produtoId]);
        } else {
            $cart[$produtoId] = min(20, $qty);
        }
        Session::set('cart', $cart);
    }

    public static function remove(int $produtoId): void
    {
        $cart = self::items();
        unset($cart[$produtoId]);
        Session::set('cart', $cart);
    }

    public static function clear(): void
    {
        Session::set('cart', []);
    }

    public static function count(): int
    {
        return array_sum(self::items());
    }

    public static function detailed(): array
    {
        $items = [];
        $total = 0.0;
        foreach (self::items() as $id => $qty) {
            $produto = Produto::find((int) $id);
            if (!$produto || $produto['status'] !== 'ativo') {
                continue;
            }
            $preco = (float) ($produto['preco_promocional'] ?: $produto['preco']);
            $sub = $preco * (int) $qty;
            $total += $sub;
            $produto['imagem'] = Produto::capa($produto);
            $items[] = [
                'produto' => $produto,
                'qty' => (int) $qty,
                'preco' => $preco,
                'subtotal' => $sub,
            ];
        }
        return ['items' => $items, 'total' => $total];
    }
}
