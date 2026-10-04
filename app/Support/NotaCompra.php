<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Configuracao;

/**
 * Recibo de compra enviado por e-mail quando o pagamento entra.
 */
final class NotaCompra
{
    public static function liberada(array $pedido): bool
    {
        return in_array((string) ($pedido['status'] ?? ''), ['pago', 'enviado', 'entregue'], true);
    }

    public static function numero(array $pedido): string
    {
        $id = (int) ($pedido['id'] ?? 0);
        return 'REC-' . str_pad((string) max(1, $id), 6, '0', STR_PAD_LEFT);
    }

    public static function html(array $pedido, bool $imprimir = false, string $extra = ''): string
    {
        $codigo = (string) ($pedido['codigo'] ?? '');
        $numero = self::numero($pedido);
        $data = self::data($pedido);
        $digital = pedido_so_digital($pedido);
        $logo = self::logoSrc();
        $loja = Configuracao::loja();
        $cnpj = $loja['cnpj'];
        $razao = $loja['razao'] !== '' ? $loja['razao'] : 'Elomiah';
        $ie = $loja['ie'];
        $endAtelie = $loja['endereco'];

        $acoes = '';
        if ($imprimir) {
            $acoes = '<p class="nota-acoes no-print">'
                . '<button type="button" onclick="window.print()">Imprimir</button>'
                . '<a href="' . e(url('/pedido/' . $codigo)) . '">Voltar ao pedido</a>'
                . '</p>';
        }

        return '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8">'
            . '<meta name="viewport" content="width=device-width, initial-scale=1">'
            . '<title>Recibo ' . e($numero) . ' · Elomiah</title>'
            . ($imprimir ? self::printCss() : '')
            . '</head><body style="margin:0;padding:0;background:#FDFBF6;color:#1B4332">'
            . '<div style="max-width:640px;margin:0 auto;padding:40px 28px 64px;font-family:Georgia,\'Times New Roman\',serif">'
            . $acoes
            . '<p style="margin:0 0 4px;letter-spacing:.38em;font-size:11px;color:#C9A24B">ELOMIAH</p>'
            . '<p style="margin:0 0 28px;font-size:13px;color:#5C6B61">Onde o sagrado encontra a essência.</p>'
            . '<h1 style="margin:0 0 8px;font-size:28px;font-weight:400;letter-spacing:.04em">Recibo de compra</h1>'
            . '<p style="margin:0 0 28px;font-size:14px;color:#5C6B61">' . e($numero) . ' · Pedido ' . e($codigo) . ' · ' . e($data) . '</p>'
            . self::emitente($razao, $cnpj, $ie, $endAtelie)
            . self::destinatario($pedido)
            . self::itens($pedido)
            . self::totais($pedido, $digital)
            . self::pagamento($pedido)
            . ($digital ? '<p style="margin:0 0 22px;font-size:13px;color:#5C6B61">Formação digital · sem despacho.</p>' : self::destino($pedido))
            . $extra
            . '<div style="text-align:center;margin-top:48px;padding-top:32px;border-top:1px solid #E8E2D6">'
            . '<img src="' . e($logo) . '" alt="Elomiah" width="132" style="width:132px;height:auto;margin:0 0 12px;display:inline-block">'
            . '<p style="margin:0 0 6px;letter-spacing:.38em;font-size:11px;color:#C9A24B">ELOMIAH</p>'
            . '<p style="margin:0;font-size:13px;color:#5C6B61">Onde o sagrado encontra a essência.</p>'
            . '</div>'
            . '<p style="margin:36px 0 0;font-size:11px;line-height:1.6;color:#8A938C">Este é o seu recibo de compra. Obrigado por viver o ritual Elomiah.</p>'
            . '</div></body></html>';
    }

    private static function emitente(string $razao, string $cnpj, string $ie, string $endereco): string
    {
        $html = '<p style="margin:0 0 6px;font-size:12px;letter-spacing:.22em;text-transform:uppercase;color:#C9A24B">Emitente</p>'
            . '<p style="margin:0 0 22px;font-size:14px;line-height:1.6;color:#1B4332">' . e($razao);
        if ($cnpj !== '') {
            $html .= '<br>CNPJ ' . e($cnpj);
        }
        if ($ie !== '') {
            $html .= '<br>IE ' . e($ie);
        }
        if ($endereco !== '') {
            $html .= '<br>' . e($endereco);
        }
        $html .= '</p>';

        return $html;
    }

    /** @param array<string, mixed> $pedido */
    private static function destinatario(array $pedido): string
    {
        return '<p style="margin:0 0 6px;font-size:12px;letter-spacing:.22em;text-transform:uppercase;color:#C9A24B">Destinatário</p>'
            . '<p style="margin:0 0 22px;font-size:14px;line-height:1.6;color:#1B4332">'
            . e((string) ($pedido['nome_cliente'] ?? ''))
            . '<br>' . e((string) ($pedido['email_cliente'] ?? ''))
            . (!empty($pedido['telefone_cliente']) ? '<br>' . e((string) $pedido['telefone_cliente']) : '')
            . '</p>';
    }

    /** @param array<string, mixed> $pedido */
    private static function itens(array $pedido): string
    {
        $linhas = '<tr>'
            . '<td style="padding:8px 0;border-bottom:1px solid #E8E2D6;font-size:11px;letter-spacing:.18em;text-transform:uppercase;color:#C9A24B">Item</td>'
            . '<td style="padding:8px 0;border-bottom:1px solid #E8E2D6;font-size:11px;letter-spacing:.18em;text-transform:uppercase;color:#C9A24B;text-align:center">Qtd</td>'
            . '<td style="padding:8px 0;border-bottom:1px solid #E8E2D6;font-size:11px;letter-spacing:.18em;text-transform:uppercase;color:#C9A24B;text-align:right">Valor</td>'
            . '</tr>';
        foreach ($pedido['itens'] ?? [] as $item) {
            $linhas .= '<tr>'
                . '<td style="padding:12px 0;border-bottom:1px solid #E8E2D6;color:#1B4332">'
                . e((string) ($item['nome_produto'] ?? ''))
                . '</td>'
                . '<td style="padding:12px 0;border-bottom:1px solid #E8E2D6;text-align:center;color:#1B4332">'
                . (int) ($item['quantidade'] ?? 1)
                . '</td>'
                . '<td style="padding:12px 0;border-bottom:1px solid #E8E2D6;text-align:right;color:#C9A24B;font-family:Georgia,serif">'
                . e(money($item['subtotal'] ?? 0))
                . '</td></tr>';
        }

        return '<table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;margin:0 0 18px">' . $linhas . '</table>';
    }

    /** @param array<string, mixed> $pedido */
    private static function totais(array $pedido, bool $digital): string
    {
        $envio = $digital ? 'Acesso digital' : ((float) ($pedido['frete'] ?? 0) <= 0 ? 'Frete grátis' : 'Envio · ' . frete()['nome']);

        return '<p style="margin:0 0 4px;font-size:14px;color:#5C6B61">Subtotal ' . e(money($pedido['subtotal'] ?? 0)) . '</p>'
            . '<p style="margin:0 0 8px;font-size:14px;color:#5C6B61">' . e($envio) . ' · ' . e(money($pedido['frete'] ?? 0)) . '</p>'
            . ((float) ($pedido['desconto'] ?? 0) > 0 ? '<p style="margin:0 0 8px;font-size:14px;color:#5C6B61">Desconto no Pix · − ' . e(money($pedido['desconto'])) . '</p>' : '')
            . '<p style="margin:0 0 22px;font-size:22px;font-family:Georgia,serif;color:#1B4332">Total ' . e(money($pedido['total'] ?? 0)) . '</p>';
    }

    /** @param array<string, mixed> $pedido */
    private static function pagamento(array $pedido): string
    {
        $metodo = pagamento_rotulo($pedido['metodo_pagamento'] ?? 'pix');
        $extra = '';
        if (($pedido['metodo_pagamento'] ?? '') === 'cartao' && !empty($pedido['cartao_bandeira'])) {
            $extra .= ' · ' . e(\App\Support\CartaoCredito::bandeiraRotulo((string) $pedido['cartao_bandeira']));
            if (!empty($pedido['cartao_final'])) {
                $extra .= ' final ' . e((string) $pedido['cartao_final']);
            }
        }

        return '<p style="margin:0 0 22px;font-size:14px;color:#5C6B61">Pagamento ' . e($metodo) . $extra . ' · quitado</p>';
    }

    /** @param array<string, mixed> $pedido */
    private static function destino(array $pedido): string
    {
        $end = $pedido['endereco'] ?? null;
        if (!is_array($end)) {
            return '';
        }

        return '<p style="margin:0 0 6px;font-size:12px;letter-spacing:.22em;text-transform:uppercase;color:#C9A24B">Destino</p>'
            . '<p style="margin:0 0 22px;font-size:14px;line-height:1.6;color:#1B4332">'
            . e((string) ($end['logradouro'] ?? '')) . ', ' . e((string) ($end['numero'] ?? ''))
            . (!empty($end['complemento']) ? ' · ' . e((string) $end['complemento']) : '')
            . '<br>' . e((string) ($end['bairro'] ?? '')) . ' — ' . e((string) ($end['cidade'] ?? '')) . '/' . e((string) ($end['estado'] ?? ''))
            . '<br>CEP ' . e(cep_format((string) ($end['cep'] ?? '')))
            . '</p>';
    }

    /** @param array<string, mixed> $pedido */
    private static function data(array $pedido): string
    {
        $raw = (string) ($pedido['pago_em'] ?? $pedido['created_at'] ?? now());
        $ts = strtotime($raw) ?: time();

        return date('d/m/Y', $ts);
    }

    private static function logoSrc(): string
    {
        $path = PUBLIC_PATH . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'logo.png';
        if (is_file($path)) {
            $bin = (string) file_get_contents($path);
            if ($bin !== '' && strlen($bin) < 180000) {
                return 'data:image/png;base64,' . base64_encode($bin);
            }
        }

        return asset('images/logo.png');
    }

    private static function printCss(): string
    {
        return '<style>
            body{background:#FDFBF6;color:#1B4332}
            .nota-acoes{margin:0 0 28px}
            .nota-acoes button,.nota-acoes a{margin-right:12px;font:11px/1 Inter,sans-serif;letter-spacing:.16em;text-transform:uppercase;background:transparent;border:0;border-bottom:1px solid #C9A24B;color:#1B4332;padding:8px 0;cursor:pointer;text-decoration:none}
            @media print{
              .no-print{display:none!important}
              body{background:#fff}
              a{color:#1B4332;text-decoration:none}
            }
        </style>';
    }
}
