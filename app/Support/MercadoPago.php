<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Configuracao;
use App\Models\Notificacao;
use App\Models\Pedido;
use RuntimeException;

/**
 * Cobrança real via Checkout Pro do Mercado Pago (Pix e cartão).
 * Número do cartão nunca transita pelo site — o cliente paga no Mercado Pago.
 */
final class MercadoPago
{
    public static function configurado(): bool
    {
        return self::token() !== '';
    }

    public static function chavePublica(): string
    {
        static $key = null;
        if ($key !== null) {
            return $key;
        }
        $env = trim((string) (config('app')['mercadopago_public_key'] ?? ''));
        if ($env !== '') {
            return $key = $env;
        }

        return $key = trim((string) Configuracao::get('mercadopago_public_key', ''));
    }

    public static function cartaoNoSite(): bool
    {
        return self::configurado() && self::chavePublica() !== '';
    }

    public static function criarPreferencia(array $pedido, array $itensCarrinho, float $frete, string $metodo): array
    {
        $items = [];
        foreach ($itensCarrinho as $item) {
            $items[] = [
                'id' => (string) ($item['produto']['id'] ?? ''),
                'title' => (string) ($item['produto']['nome'] ?? 'Elomiah'),
                'quantity' => (int) $item['qty'],
                'currency_id' => 'BRL',
                'unit_price' => self::money((float) $item['preco']),
            ];
        }
        if ($frete > 0) {
            $items[] = [
                'id' => 'frete',
                'title' => 'Envio',
                'quantity' => 1,
                'currency_id' => 'BRL',
                'unit_price' => self::money($frete),
            ];
        }

        $payload = [
            'items' => $items,
            'payer' => [
                'name' => (string) ($pedido['nome_cliente'] ?? ''),
                'email' => (string) ($pedido['email_cliente'] ?? ''),
            ],
            'external_reference' => (string) $pedido['codigo'],
            'statement_descriptor' => 'ELOMIAH',
            'binary_mode' => $metodo === 'cartao',
            'payment_methods' => self::metodos($metodo),
            'metadata' => [
                'pedido' => (string) $pedido['codigo'],
            ],
        ];

        $public = self::urlPublica();
        if (str_starts_with($public, 'https://')) {
            $retorno = $public . '/pagamento/retorno';
            $payload['back_urls'] = [
                'success' => $retorno,
                'pending' => $retorno,
                'failure' => $retorno,
            ];
            $payload['auto_return'] = 'approved';
            $payload['notification_url'] = $public . '/webhooks/mercadopago';
        }

        return self::request('POST', '/checkout/preferences', $payload);
    }

    /**
     * Pix cobrado na API: o Mercado Pago avisa quando cai, a página confirma sozinha.
     *
     * @param array<string, mixed> $pedido
     * @return array<string, mixed>
     */
    public static function criarPix(array $pedido, float $total): array
    {
        $nome = trim((string) ($pedido['nome_cliente'] ?? 'Cliente'));
        $partes = preg_split('/\s+/', $nome) ?: ['Cliente'];
        $primeiro = (string) ($partes[0] ?? 'Cliente');
        $ultimo = count($partes) > 1 ? (string) $partes[array_key_last($partes)] : 'Elomiah';

        $payload = [
            'transaction_amount' => self::money($total),
            'description' => 'Elomiah ' . (string) $pedido['codigo'],
            'payment_method_id' => 'pix',
            'external_reference' => (string) $pedido['codigo'],
            'date_of_expiration' => date('Y-m-d\TH:i:s.000P', time() + 86400),
            'payer' => [
                'email' => (string) $pedido['email_cliente'],
                'first_name' => $primeiro,
                'last_name' => $ultimo,
            ],
        ];

        $public = self::urlPublica();
        if (str_starts_with($public, 'https://')) {
            $payload['notification_url'] = $public . '/webhooks/mercadopago';
        }

        return self::request('POST', '/v1/payments', $payload);
    }

    /**
     * @param array<string, mixed> $payment
     * @return array{id:string, qr_code:string, qr_base64:string, status:string}
     */
    public static function dadosPix(array $payment): array
    {
        $tx = $payment['point_of_interaction']['transaction_data'] ?? [];
        if (!is_array($tx)) {
            $tx = [];
        }
        return [
            'id' => (string) ($payment['id'] ?? ''),
            'qr_code' => (string) ($tx['qr_code'] ?? ''),
            'qr_base64' => (string) ($tx['qr_code_base64'] ?? ''),
            'status' => (string) ($payment['status'] ?? ''),
        ];
    }

    public static function pagamento(string $id): ?array
    {
        if ($id === '' || $id === 'null') {
            return null;
        }
        try {
            return self::request('GET', '/v1/payments/' . rawurlencode($id));
        } catch (RuntimeException) {
            return null;
        }
    }

    public static function sincronizarPedidoPendente(array $pedido): void
    {
        if (($pedido['status'] ?? '') !== 'pendente' || !self::configurado()) {
            return;
        }
        $id = (string) ($pedido['mp_payment_id'] ?? '');
        if ($id !== '') {
            self::sincronizarPagamento($id);
            return;
        }
        self::sincronizarPorReferencia((string) ($pedido['codigo'] ?? ''));
    }

    public static function sincronizarPorReferencia(string $codigo): void
    {
        if ($codigo === '' || !self::configurado()) {
            return;
        }
        try {
            $json = self::request(
                'GET',
                '/v1/payments/search?sort=date_created&criteria=desc&external_reference=' . rawurlencode($codigo)
            );
        } catch (RuntimeException) {
            return;
        }
        $results = $json['results'] ?? [];
        if (!is_array($results) || $results === []) {
            return;
        }
        $escolhido = null;
        foreach ($results as $payment) {
            if (($payment['status'] ?? '') === 'approved') {
                $escolhido = $payment;
                break;
            }
            $escolhido ??= $payment;
        }
        $id = (string) ($escolhido['id'] ?? '');
        if ($id !== '') {
            self::sincronizarPagamento($id);
        }
    }

    public static function sincronizarPagamento(string $paymentId): void
    {
        $payment = self::pagamento($paymentId);
        if (!$payment) {
            return;
        }

        $codigo = (string) ($payment['external_reference'] ?? '');
        $pedido = $codigo !== '' ? Pedido::firstWhere('codigo', $codigo) : null;
        if (!$pedido) {
            return;
        }

        $statusMp = (string) ($payment['status'] ?? '');
        $novo = match ($statusMp) {
            'approved' => 'pago',
            'pending', 'authorized', 'in_process', 'in_mediation' => 'pendente',
            'rejected', 'cancelled', 'refunded', 'charged_back' => 'cancelado',
            default => null,
        };
        if ($novo === null) {
            return;
        }

        $atual = (string) $pedido['status'];
        if (in_array($atual, ['enviado', 'entregue', 'cancelado'], true)) {
            Pedido::updateById((int) $pedido['id'], [
                'mp_payment_id' => (string) ($payment['id'] ?? $paymentId),
                'mp_status' => $statusMp,
            ]);
            return;
        }

        Pedido::garantirColunas();
        if ($novo !== $atual) {
            Pedido::mudarStatus((int) $pedido['id'], $novo);
            if ($novo === 'pago') {
                Notificacao::pedidoPago(
                    (int) $pedido['id'],
                    (string) $pedido['codigo'],
                    (string) ($pedido['nome_cliente'] ?? ''),
                    (float) ($pedido['total'] ?? 0)
                );
                PedidoMail::pago($pedido);
            }
        }

        $dados = [
            'mp_payment_id' => (string) ($payment['id'] ?? $paymentId),
            'mp_status' => $statusMp,
        ];

        $tipo = (string) ($payment['payment_type_id'] ?? '');
        $metodoId = (string) ($payment['payment_method_id'] ?? '');
        if ($tipo === 'credit_card' || $tipo === 'debit_card') {
            $dados['metodo_pagamento'] = 'cartao';
            $dados['cartao_bandeira'] = $metodoId !== '' ? $metodoId : 'cartao';
            $final = (string) ($payment['card']['last_four_digits'] ?? '');
            $dados['cartao_final'] = $final !== '' ? substr($final, -4) : null;
            $dados['parcelas'] = (int) ($payment['installments'] ?? 1);
        } elseif ($metodoId === 'pix' || $tipo === 'bank_transfer') {
            $dados['metodo_pagamento'] = 'pix';
        }

        Pedido::updateById((int) $pedido['id'], $dados);
    }

    /**
     * @param array<string, mixed> $pedido
     * @param array<string, mixed> $card
     * @return array<string, mixed>
     */
    public static function criarPagamentoCartao(array $pedido, array $card): array
    {
        $token = trim((string) ($card['token'] ?? ''));
        $metodo = trim((string) ($card['payment_method_id'] ?? ''));
        if ($token === '' || $metodo === '') {
            throw new RuntimeException('Não foi possível ler o cartão. Tente de novo.');
        }

        $payload = [
            'transaction_amount' => self::money((float) ($pedido['total'] ?? 0)),
            'token' => $token,
            'description' => 'Elomiah ' . (string) ($pedido['codigo'] ?? ''),
            'installments' => max(1, (int) ($card['installments'] ?? 1)),
            'payment_method_id' => $metodo,
            'external_reference' => (string) ($pedido['codigo'] ?? ''),
            'binary_mode' => true,
            'payer' => [
                'email' => (string) ($pedido['email_cliente'] ?? ''),
            ],
        ];

        $issuer = $card['issuer_id'] ?? '';
        if ($issuer !== '' && $issuer !== null) {
            $payload['issuer_id'] = (int) $issuer;
        }

        $id = $card['payer']['identification'] ?? [];
        if (is_array($id) && trim((string) ($id['number'] ?? '')) !== '') {
            $payload['payer']['identification'] = [
                'type' => (string) ($id['type'] ?? 'CPF'),
                'number' => preg_replace('/\D+/', '', (string) $id['number']) ?? '',
            ];
        }

        $public = self::urlPublica();
        if (str_starts_with($public, 'https://')) {
            $payload['notification_url'] = $public . '/webhooks/mercadopago';
        }

        return self::request('POST', '/v1/payments', $payload);
    }

    public static function linkPagamento(array $preference): string
    {
        $sandbox = str_starts_with(self::token(), 'TEST-');
        if ($sandbox) {
            return (string) ($preference['sandbox_init_point'] ?? $preference['init_point'] ?? '');
        }
        return (string) ($preference['init_point'] ?? $preference['sandbox_init_point'] ?? '');
    }

    public static function urlPublica(): string
    {
        $custom = (string) (config('app')['mercadopago_public_url'] ?? '');
        if ($custom !== '') {
            return $custom;
        }
        return rtrim((string) (config('app')['url'] ?? ''), '/');
    }

    /**
     * @return array<string, mixed>
     */
    private static function metodos(string $metodo): array
    {
        if ($metodo === 'pix') {
            return [
                'excluded_payment_types' => [
                    ['id' => 'credit_card'],
                    ['id' => 'debit_card'],
                    ['id' => 'ticket'],
                    ['id' => 'atm'],
                ],
                'default_payment_method_id' => 'pix',
            ];
        }

        return [
            'excluded_payment_types' => [
                ['id' => 'ticket'],
                ['id' => 'atm'],
                ['id' => 'bank_transfer'],
            ],
            'installments' => 6,
        ];
    }

    /**
     * @param array<string, mixed>|null $body
     * @return array<string, mixed>
     */
    private static function request(string $method, string $path, ?array $body = null): array
    {
        $token = self::token();
        if ($token === '') {
            throw new RuntimeException('Mercado Pago não configurado.');
        }
        if (!function_exists('curl_init')) {
            throw new RuntimeException('Ative a extensão curl do PHP para cobrar pelo Mercado Pago.');
        }

        $ch = curl_init('https://api.mercadopago.com' . $path);
        if ($ch === false) {
            throw new RuntimeException('Não foi possível iniciar a conexão com o Mercado Pago.');
        }

        $headers = [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'Accept: application/json',
            'X-Idempotency-Key: ' . bin2hex(random_bytes(16)),
        ];

        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => $method,
        ];
        if ($body !== null) {
            $opts[CURLOPT_POSTFIELDS] = json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        curl_setopt_array($ch, $opts);
        $raw = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($raw === false) {
            throw new RuntimeException('Falha de rede no Mercado Pago: ' . $err);
        }

        $json = json_decode((string) $raw, true);
        if (!is_array($json)) {
            throw new RuntimeException('Resposta inválida do Mercado Pago.');
        }
        if ($code >= 400) {
            $msg = (string) ($json['message'] ?? $json['error'] ?? 'Erro no Mercado Pago.');
            throw new RuntimeException($msg);
        }
        return $json;
    }

    private static function token(): string
    {
        static $token = null;
        if ($token !== null) {
            return $token;
        }
        $env = trim((string) (config('app')['mercadopago_access_token'] ?? ''));
        if ($env !== '') {
            return $token = $env;
        }

        return $token = trim((string) Configuracao::get('mercadopago_access_token', ''));
    }

    private static function money(float $value): float
    {
        return (float) number_format($value, 2, '.', '');
    }
}
