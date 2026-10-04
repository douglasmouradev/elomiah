<?php

declare(strict_types=1);

/**
 * Funções auxiliares globais da Elomiah.
 */

use App\Core\Csrf;
use App\Core\Session;

function env(string $key, mixed $default = null): mixed
{
    if (array_key_exists($key, $_ENV)) {
        return $_ENV[$key];
    }
    $value = getenv($key);
    return $value === false ? $default : $value;
}

function config(string $file): array
{
    static $cache = [];
    if (!isset($cache[$file])) {
        $path = CONFIG_PATH . DIRECTORY_SEPARATOR . $file . '.php';
        $cache[$file] = is_file($path) ? require $path : [];
    }
    return $cache[$file];
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path = '/'): string
{
    $base = rtrim((string) (config('app')['url'] ?? ''), '/');
    $path = '/' . ltrim($path, '/');
    $path = $path === '/' ? '/' : rtrim($path, '/');

    // Hosts que engolem a rota (ex.: InfinityFree): index.php?__path=entrar
    if (
        env('APP_QUERY_ROUTES', '0') === '1'
        && $path !== '/'
        && !str_starts_with($path, '/assets/')
    ) {
        return $base . '/index.php?__path=' . rawurlencode(ltrim($path, '/'));
    }

    return $base . ($path === '/' ? '/' : $path);
}

/** Prefixo da URL (ex.: /public) quando o site não está na raiz do domínio. */
function app_base_path(): string
{
    static $base = null;
    if ($base !== null) {
        return $base;
    }
    $path = parse_url((string) (config('app')['url'] ?? ''), PHP_URL_PATH);
    $base = rtrim((string) ($path ?: ''), '/');

    return $base;
}

function asset(string $path): string
{
    return url('/assets/' . ltrim($path, '/'));
}

function current_path(): string
{
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $prefix = app_base_path();
    if ($prefix !== '' && ($uri === $prefix || str_starts_with($uri, $prefix . '/'))) {
        $uri = substr($uri, strlen($prefix)) ?: '/';
    }
    return rtrim($uri, '/') ?: '/';
}

function is_active(string $path): bool
{
    $current = current_path();
    $path = rtrim($path, '/') ?: '/';
    if ($path === '/') {
        return $current === '/';
    }
    return $current === $path || str_starts_with($current, $path . '/');
}

function csrf_token(): string
{
    return Csrf::token();
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(Csrf::token()) . '">';
}

function method_field(string $method): string
{
    return '<input type="hidden" name="_method" value="' . e(strtoupper($method)) . '">';
}

function old(string $key, mixed $default = ''): mixed
{
    $old = Session::get('_old', []);
    return $old[$key] ?? $default;
}

function flash(string $key, mixed $default = null): mixed
{
    return Session::flash($key, $default);
}

/** essenciais | todos | recusar | vazio se ainda não escolheu */
function cookie_consentimento(): string
{
    $escolha = (string) ($_COOKIE['elomiah_cookies'] ?? '');

    return in_array($escolha, ['essenciais', 'todos', 'recusar'], true) ? $escolha : '';
}

function money(float|int|string|null $value): string
{
    return 'R$ ' . number_format((float) $value, 2, ',', '.');
}

function cep_format(?string $cep): string
{
    $digitos = preg_replace('/\D+/', '', (string) $cep) ?? '';
    if (strlen($digitos) === 8) {
        return substr($digitos, 0, 5) . '-' . substr($digitos, 5);
    }

    return trim((string) $cep);
}

/** @return array{valor: float, nome: string, prazo: string} */
function frete(): array
{
    return \App\Models\Configuracao::frete();
}

function produto_digital(?array $produto): bool
{
    if (!$produto) {
        return false;
    }
    $slug = (string) ($produto['slug'] ?? '');
    $cat = (string) ($produto['categoria_slug'] ?? '');
    $sku = (string) ($produto['sku'] ?? '');

    return $slug === 'o-ritual-das-essencias' || $cat === 'formacao' || $sku === 'ELO-CURSO';
}

function carrinho_requer_envio(array $carrinho): bool
{
    $items = $carrinho['items'] ?? [];
    if ($items === []) {
        return true;
    }
    foreach ($items as $item) {
        if (!produto_digital($item['produto'] ?? null)) {
            return true;
        }
    }

    return false;
}

/** @return array{valor: float, nome: string, prazo: string} */
function frete_do_carrinho(array $carrinho): array
{
    $frete = frete();
    if (carrinho_requer_envio($carrinho)) {
        $limite = \App\Models\Configuracao::vitrine()['frete_gratis'];
        if ($limite > 0 && (float) ($carrinho['total'] ?? 0) >= $limite) {
            return ['valor' => 0.0, 'nome' => 'Frete grátis', 'prazo' => $frete['prazo']];
        }
        return $frete;
    }

    return [
        'valor' => 0.0,
        'nome' => 'Acesso digital',
        'prazo' => 'Sem despacho · o acesso libera nesta conta depois do pagamento',
    ];
}

/** @return array{ativo: bool, limite: float, falta: float, pct: int, atingido: bool} */
function frete_gratis_progresso(array $carrinho): array
{
    $limite = \App\Models\Configuracao::vitrine()['frete_gratis'];
    $total = (float) ($carrinho['total'] ?? 0);
    $ativo = $limite > 0 && carrinho_requer_envio($carrinho);

    return [
        'ativo' => $ativo,
        'limite' => $limite,
        'falta' => $ativo ? max(0, $limite - $total) : 0.0,
        'pct' => $ativo ? (int) min(100, round($total / $limite * 100)) : 0,
        'atingido' => $ativo && $total >= $limite,
    ];
}

function formas_pagamento(): string
{
    return \App\Support\MercadoPago::configurado() ? 'Pix ou cartão' : 'Pix';
}

function desconto_pix(): float
{
    if (!\App\Support\MercadoPago::configurado() && !\App\Support\Pix::configurado()) {
        return 0.0;
    }
    return \App\Models\Configuracao::vitrine()['desconto_pix'];
}

function valor_desconto_pix(float $total): float
{
    return round($total * desconto_pix() / 100, 2);
}

function pct(float $valor): string
{
    return rtrim(rtrim(number_format($valor, 1, ',', ''), '0'), ',') . '%';
}

/** @return array{pix: float|null, parcelas: int, parcela: float} */
function preco_pagamento(float $preco): array
{
    $desconto = desconto_pix();
    $parcelas = \App\Support\MercadoPago::configurado() ? \App\Models\Configuracao::vitrine()['parcelas'] : 0;
    $parcelas = (int) min($parcelas, floor($preco / 5));

    return [
        'pix' => $desconto > 0 ? round($preco * (1 - $desconto / 100), 2) : null,
        'parcelas' => $parcelas > 1 ? $parcelas : 0,
        'parcela' => $parcelas > 1 ? round($preco / $parcelas, 2) : 0.0,
    ];
}

/** @return array{ativo: bool, texto: string, falta: float, atingido: bool} */
function brinde_progresso(array $carrinho): array
{
    $v = \App\Models\Configuracao::vitrine();
    $ativo = $v['brinde_acima'] > 0 && $v['brinde_texto'] !== '' && carrinho_requer_envio($carrinho);
    $total = (float) ($carrinho['total'] ?? 0);

    return [
        'ativo' => $ativo,
        'texto' => $v['brinde_texto'],
        'falta' => $ativo ? max(0, $v['brinde_acima'] - $total) : 0.0,
        'atingido' => $ativo && $total >= $v['brinde_acima'],
    ];
}

/** @return list<array{p: string, r: string}> */
function faq_loja(): array
{
    $faq = \App\Models\Configuracao::vitrine()['faq'];
    if ($faq !== null) {
        return $faq;
    }
    $frete = frete();

    return [
        ['p' => 'Em quanto tempo o pedido chega?', 'r' => rtrim($frete['prazo'], '. ') . '. Você acompanha o andamento em Meus pedidos.'],
        ['p' => 'Quais são as formas de pagamento?', 'r' => formas_pagamento() . '. No Pix, o QR aparece na tela do pedido assim que você finaliza.'],
        ['p' => 'Posso trocar ou devolver?', 'r' => 'Sim. Você tem 7 dias, contados do recebimento, para desistir da compra. Fale com a gente pelo WhatsApp ou pelo formulário de contato com o código do pedido.'],
        ['p' => 'Como usar o spray de ambiente?', 'r' => 'Borrife no ar ou em tecidos, a cerca de 20 cm. Não aplique nos olhos e não ingira.'],
        ['p' => 'Como acesso o curso O Ritual das Essências?', 'r' => 'Depois que o pagamento é confirmado, o acesso libera na sua conta, por 12 meses.'],
    ];
}

function slugify(string $text): string
{
    $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
    $text = strtolower((string) preg_replace('/[^a-z0-9]+/i', '-', $text));
    return trim($text, '-');
}

function redirect(string $path, int $code = 302): never
{
    http_response_code($code);
    header('Location: ' . (str_starts_with($path, 'http') ? $path : url($path)));
    exit;
}

function client_ip(): string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    return filter_var($ip, FILTER_VALIDATE_IP) ?: '0.0.0.0';
}

function now(): string
{
    return date('Y-m-d H:i:s');
}

function str_limit(?string $value, int $limit = 120): string
{
    $value = trim((string) $value);
    if (mb_strlen($value) <= $limit) {
        return $value;
    }
    return rtrim(mb_substr($value, 0, $limit)) . '…';
}

function is_post(): bool
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
}

function whatsapp_url(string $message = ''): string
{
    $number = config('app')['whatsapp'] ?? '5571984916767';
    $q = $message !== '' ? '?text=' . rawurlencode($message) : '';
    return 'https://wa.me/' . $number . $q;
}

function shopee_url(): string
{
    return (string) (config('app')['shopee_url'] ?? 'https://collshp.com/geovanaferreira030789?view=storefront');
}

function pagamento_rotulo(?string $metodo): string
{
    return match ($metodo) {
        'cartao' => 'Cartão de crédito',
        'pix' => 'Pix',
        default => (string) $metodo,
    };
}

function pedido_tem_formacao(array $pedido): bool
{
    foreach ($pedido['itens'] ?? [] as $item) {
        if (produto_digital($item) || produto_digital(['sku' => $item['sku'] ?? '', 'slug' => $item['slug'] ?? ''])) {
            return true;
        }
    }

    return false;
}

function pedido_so_digital(array $pedido): bool
{
    $itens = $pedido['itens'] ?? [];
    if ($itens === []) {
        return empty($pedido['endereco']) && (float) ($pedido['frete'] ?? 0) < 0.01;
    }
    foreach ($itens as $item) {
        $produto = $item;
        if (!produto_digital($produto)) {
            $produto = [
                'sku' => (string) ($item['sku'] ?? ''),
                'slug' => (string) ($item['slug'] ?? ''),
                'categoria_slug' => (string) ($item['categoria_slug'] ?? ''),
            ];
        }
        if (!produto_digital($produto)) {
            return false;
        }
    }

    return true;
}

function pedido_status_rotulo(?string $status, bool $digital = false): string
{
    if ($digital) {
        return match ($status) {
            'pendente' => 'Aguardando pagamento',
            'pago', 'enviado', 'entregue' => 'Acesso liberado',
            'cancelado' => 'Cancelado',
            default => (string) $status,
        };
    }

    return match ($status) {
        'pendente' => 'Aguardando pagamento',
        'pago' => 'Pago',
        'enviado' => 'Enviado',
        'entregue' => 'Entregue',
        'cancelado' => 'Cancelado',
        default => (string) $status,
    };
}

function pedido_status_acao(?string $status, bool $digital = false): ?array
{
    if ($digital) {
        return match ($status) {
            'pendente' => ['status' => 'pago', 'rotulo' => 'Marcar como pago'],
            default => null,
        };
    }

    return match ($status) {
        'pendente' => ['status' => 'pago', 'rotulo' => 'Marcar como pago'],
        'pago' => ['status' => 'enviado', 'rotulo' => 'Marcar como enviado'],
        'enviado' => ['status' => 'entregue', 'rotulo' => 'Marcar como entregue'],
        default => null,
    };
}

function rastreio_url(?string $codigo, ?string $transportadora = null): ?string
{
    $codigo = trim((string) $codigo);
    if ($codigo === '') {
        return null;
    }
    $via = mb_strtolower((string) $transportadora);
    if ($via === '' || str_contains($via, 'correio')) {
        return 'https://www.linkcorreios.com.br/?id=' . rawurlencode($codigo);
    }

    return null;
}

function pedido_whatsapp_cliente(?string $telefone, string $mensagem): string
{
    $tel = preg_replace('/\D+/', '', (string) $telefone) ?? '';
    if (strlen($tel) < 10) {
        return whatsapp_url($mensagem);
    }
    if (!str_starts_with($tel, '55')) {
        $tel = '55' . $tel;
    }
    return 'https://wa.me/' . $tel . '?text=' . rawurlencode($mensagem);
}
