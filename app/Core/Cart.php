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
        self::update($produtoId, self::qty($produtoId) + max(1, $qty));
    }

    public static function update(int $produtoId, int $qty): void
    {
        $cart = self::items();
        if ($qty <= 0) {
            unset($cart[$produtoId]);
            Session::set('cart', $cart);
            return;
        }

        $teto = self::teto($produtoId);
        if ($teto < 1) {
            unset($cart[$produtoId]);
        } else {
            $cart[$produtoId] = min($teto, $qty);
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

    public static function qty(int $produtoId): int
    {
        return (int) (self::items()[$produtoId] ?? 0);
    }

    public static function teto(int $produtoId): int
    {
        $produto = Produto::find($produtoId);
        if (!$produto || ($produto['status'] ?? '') !== 'ativo') {
            return 0;
        }
        if (produto_digital($produto)) {
            return 1;
        }

        return max(0, min(20, (int) ($produto['estoque'] ?? 0)));
    }

    public static function detailed(): array
    {
        $items = [];
        $total = 0.0;
        $cart = self::items();
        $ajustado = false;
        foreach ($cart as $id => $qty) {
            $produto = Produto::find((int) $id);
            if (!$produto || $produto['status'] !== 'ativo') {
                unset($cart[$id]);
                $ajustado = true;
                continue;
            }
            $teto = self::teto((int) $id);
            if ($teto < 1) {
                unset($cart[$id]);
                $ajustado = true;
                continue;
            }
            $qty = min($teto, max(1, (int) $qty));
            if ($qty !== (int) ($cart[$id] ?? 0)) {
                $cart[$id] = $qty;
                $ajustado = true;
            }
            $preco = (float) ($produto['preco_promocional'] ?: $produto['preco']);
            $sub = $preco * $qty;
            $total += $sub;
            $produto['imagem'] = Produto::capa($produto);
            $items[] = [
                'produto' => $produto,
                'qty' => $qty,
                'preco' => $preco,
                'subtotal' => $sub,
            ];
        }
        if ($ajustado) {
            Session::set('cart', $cart);
        }

        return ['items' => $items, 'total' => $total];
    }
}
