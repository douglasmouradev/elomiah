<?php

declare(strict_types=1);

return [
    'name' => env('APP_NAME', 'Elomiah'),
    'env' => env('APP_ENV', 'local'),
    'debug' => env('APP_DEBUG', 'false') === 'true',
    'url' => rtrim((string) env('APP_URL', 'http://localhost:8000'), '/'),
    'key' => (string) env('APP_KEY', ''),
    'whatsapp' => preg_replace('/\D+/', '', (string) env('WHATSAPP', '5571984916767')) ?: '5571984916767',
    'achadinhos_url' => (string) env('ACHADINHOS_URL', 'https://collshp.com/geovanaferreira030789?view=storefront'),
    'shopee_url' => (string) env('SHOPEE_URL', 'https://collshp.com/geovanaferreira030789?view=storefront'),
    'mercadopago_access_token' => (string) env('MERCADOPAGO_ACCESS_TOKEN', ''),
    'mercadopago_public_key' => (string) env('MERCADOPAGO_PUBLIC_KEY', ''),
    'mercadopago_public_url' => rtrim((string) env('MERCADOPAGO_PUBLIC_URL', ''), '/'),
    'pix_chave' => (string) env('PIX_CHAVE', ''),
    'pix_nome' => (string) env('PIX_NOME', 'ELOMIAH'),
    'pix_cidade' => (string) env('PIX_CIDADE', 'SAO PAULO'),
    'banco_nome' => (string) env('BANCO_NOME', 'Nu Pagamentos S.A.'),
    'banco_codigo' => (string) env('BANCO_CODIGO', '0260'),
    'banco_agencia' => (string) env('BANCO_AGENCIA', '0001'),
    'banco_conta' => (string) env('BANCO_CONTA', ''),
    'session_secure' => env('SESSION_SECURE', '0') === '1',
    'upload_max_mb' => 4,
    'rate_limit' => [
        'login' => ['max' => 5, 'window' => 900],
        'contato' => ['max' => 5, 'window' => 600],
        'checkout' => ['max' => 10, 'window' => 600],
        'lgpd' => ['max' => 3, 'window' => 3600],
        'depoimento' => ['max' => 3, 'window' => 3600],
        'recuperar' => ['max' => 3, 'window' => 3600],
    ],
];
