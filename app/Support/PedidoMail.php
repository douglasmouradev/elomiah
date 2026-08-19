<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Pedido;

final class PedidoMail
{
    public static function recebido(array $pedido): void
    {
        $pedido = self::completo($pedido);
        if (!$pedido) {
            return;
        }

        $html = self::layout(
            'Recebemos o seu pedido',
            self::intro('O ateliê recebeu o pedido ' . self::codigo($pedido) . '.')
            . self::itens($pedido)
            . self::totais($pedido)
            . self::envio($pedido)
            . self::cta('Acompanhar o pedido', url('/conta/pedidos/' . $pedido['codigo']))
            . '<p style="margin:24px 0 0;font-size:14px;line-height:1.6;color:#5C6B61">Se o pagamento ainda estiver aberto, conclua nesta mesma página. Quando entrar, avisamos por e-mail.</p>'
        );

        Mail::send(
            (string) ($pedido['email_cliente'] ?? ''),
            'Recebemos o pedido ' . self::codigo($pedido),
            $html
        );

        $admin = Mail::admin();
        $cliente = strtolower((string) ($pedido['email_cliente'] ?? ''));
        if ($admin !== '' && strcasecmp($admin, $cliente) !== 0) {
            Mail::send($admin, 'Novo pedido ' . self::codigo($pedido) . ' no ateliê', $html);
        }
    }

    public static function pago(array $pedido): void
    {
        $pedido = self::completo($pedido);
        if (!$pedido) {
            return;
        }

        $html = self::layout(
            'Pagamento confirmado',
            self::intro(
                self::soDigital($pedido)
                    ? 'O pagamento do pedido ' . self::codigo($pedido) . ' entrou. O acesso à formação chega por e-mail.'
                    : 'O pagamento do pedido ' . self::codigo($pedido) . ' entrou. O ritual segue para o despacho.'
            )
            . self::itens($pedido)
            . self::totais($pedido)
            . self::cta('Ver o pedido', url('/conta/pedidos/' . $pedido['codigo']))
        );

        Mail::send(
            (string) ($pedido['email_cliente'] ?? ''),
            'Pagamento confirmado · ' . self::codigo($pedido),
            $html
        );
    }

    public static function enviado(array $pedido): void
    {
        $pedido = self::completo($pedido);
        if (!$pedido) {
            return;
        }

        $rastreio = trim((string) ($pedido['codigo_rastreio'] ?? ''));
        $via = trim((string) ($pedido['transportadora'] ?? '')) ?: 'Correios';
        $link = rastreio_url($rastreio !== '' ? $rastreio : null, $via);

        $bloco = '<p style="margin:0 0 18px;font-size:15px;line-height:1.7;color:#1B4332">O pedido ' . e(self::codigo($pedido)) . ' saiu do ateliê pelos ' . e($via) . '.</p>';
        if ($rastreio !== '') {
            $bloco .= '<p style="margin:0 0 8px;font-size:12px;letter-spacing:.22em;text-transform:uppercase;color:#C9A24B">Rastreio</p>'
                . '<p style="margin:0 0 18px;font-size:18px;font-family:Georgia,serif;color:#1B4332">' . e($rastreio) . '</p>';
        }

        $html = self::layout(
            'Seu pedido saiu do ateliê',
            $bloco
            . self::itens($pedido)
            . ($link ? self::cta('Acompanhar nos Correios', $link) : self::cta('Ver o pedido', url('/conta/pedidos/' . $pedido['codigo'])))
        );

        Mail::send(
            (string) ($pedido['email_cliente'] ?? ''),
            'Pedido enviado · ' . self::codigo($pedido),
            $html
        );
    }

    /** @param array<string, mixed> $pedido */
    private static function completo(array $pedido): ?array
    {
        $id = (int) ($pedido['id'] ?? 0);
        if ($id > 0) {
            return Pedido::withItems($id);
        }

        return ($pedido['codigo'] ?? '') !== '' ? $pedido : null;
    }

    /** @param array<string, mixed> $pedido */
    private static function codigo(array $pedido): string
    {
        return (string) ($pedido['codigo'] ?? '');
    }

    private static function intro(string $texto): string
    {
        return '<p style="margin:0 0 22px;font-size:16px;line-height:1.7;color:#1B4332">' . e($texto) . '</p>';
    }

    /** @param array<string, mixed> $pedido */
    private static function itens(array $pedido): string
    {
        $linhas = '';
        foreach ($pedido['itens'] ?? [] as $item) {
            $linhas .= '<tr>'
                . '<td style="padding:10px 0;border-bottom:1px solid #E8E2D6;color:#1B4332">'
                . e((string) ($item['nome_produto'] ?? ''))
                . ' × ' . (int) ($item['quantidade'] ?? 1)
                . '</td>'
                . '<td style="padding:10px 0;border-bottom:1px solid #E8E2D6;text-align:right;color:#C9A24B;font-family:Georgia,serif">'
                . e(money($item['subtotal'] ?? 0))
                . '</td></tr>';
        }

        return '<table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;margin:0 0 18px">' . $linhas . '</table>';
    }

    /** @param array<string, mixed> $pedido */
    private static function totais(array $pedido): string
    {
        $rotulo = self::soDigital($pedido) ? 'Acesso digital' : ('Envio · ' . frete()['nome']);
        return '<p style="margin:0 0 6px;font-size:14px;color:#5C6B61">' . e($rotulo) . ' · ' . e(money($pedido['frete'] ?? 0)) . '</p>'
            . '<p style="margin:0 0 22px;font-size:20px;font-family:Georgia,serif;color:#1B4332">Total ' . e(money($pedido['total'] ?? 0)) . '</p>';
    }

    /** @param array<string, mixed> $pedido */
    private static function envio(array $pedido): string
    {
        if (self::soDigital($pedido)) {
            return '<p style="margin:0 0 22px;font-size:13px;color:#5C6B61">Formação digital · o acesso chega por e-mail após o pagamento.</p>';
        }

        $frete = frete();
        $end = $pedido['endereco'] ?? null;
        $html = '<p style="margin:0 0 6px;font-size:12px;letter-spacing:.22em;text-transform:uppercase;color:#C9A24B">Destino</p>';
        if (is_array($end)) {
            $html .= '<p style="margin:0 0 8px;font-size:14px;line-height:1.6;color:#1B4332">'
                . e((string) ($end['logradouro'] ?? '')) . ', ' . e((string) ($end['numero'] ?? ''))
                . (!empty($end['complemento']) ? ' · ' . e((string) $end['complemento']) : '')
                . '<br>' . e((string) ($end['bairro'] ?? '')) . ' — ' . e((string) ($end['cidade'] ?? '')) . '/' . e((string) ($end['estado'] ?? ''))
                . '<br>CEP ' . e(cep_format((string) ($end['cep'] ?? '')))
                . '</p>';
        }
        $html .= '<p style="margin:0 0 22px;font-size:13px;color:#5C6B61">' . e($frete['prazo']) . '</p>';

        return $html;
    }

    /** @param array<string, mixed> $pedido */
    private static function soDigital(array $pedido): bool
    {
        return empty($pedido['endereco']) && (float) ($pedido['frete'] ?? 0) < 0.01;
    }

    private static function cta(string $rotulo, string $href): string
    {
        return '<p style="margin:8px 0 0"><a href="' . e($href) . '" style="display:inline-block;padding:12px 22px;background:#1B4332;color:#FDFBF6;text-decoration:none;letter-spacing:.16em;text-transform:uppercase;font-size:11px">' . e($rotulo) . '</a></p>';
    }

    private static function layout(string $titulo, string $corpo): string
    {
        return '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>' . e($titulo) . '</title></head>'
            . '<body style="margin:0;padding:0;background:#FDFBF6;color:#1B4332">'
            . '<div style="max-width:560px;margin:0 auto;padding:40px 24px 56px;font-family:Georgia,\'Times New Roman\',serif">'
            . '<p style="margin:0 0 8px;letter-spacing:.38em;font-size:11px;color:#C9A24B">ELOMIAH</p>'
            . '<p style="margin:0 0 28px;font-size:13px;color:#5C6B61">Onde o sagrado encontra a essência.</p>'
            . '<h1 style="margin:0 0 20px;font-size:28px;font-weight:400;letter-spacing:.04em">' . e($titulo) . '</h1>'
            . $corpo
            . '<p style="margin:40px 0 0;font-size:12px;color:#8A938C">Este e-mail é do ateliê Elomiah. Se você não fez este pedido, ignore a mensagem.</p>'
            . '</div></body></html>';
    }
}
