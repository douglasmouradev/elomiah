<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

final class Configuracao extends Model
{
    protected static string $table = 'configuracoes';

    public static function get(string $chave, mixed $default = null): mixed
    {
        $row = self::firstWhere('chave', $chave);
        return $row['valor'] ?? $default;
    }

    public static function set(string $chave, string $valor): void
    {
        $row = self::firstWhere('chave', $chave);
        if ($row) {
            self::updateById((int) $row['id'], ['valor' => $valor]);
            return;
        }
        Database::insert('configuracoes', [
            'chave' => $chave,
            'valor' => $valor,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public static function garantir(string $chave, string $valor): void
    {
        if (self::firstWhere('chave', $chave)) {
            return;
        }
        self::set($chave, $valor);
    }

    /** @return array{valor: float, nome: string, prazo: string} */
    public static function frete(): array
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }

        self::garantir('frete_padrao', '18.90');
        self::garantir('frete_nome', 'Despacho do ateliê');
        self::garantir('frete_prazo', 'Sai em até 3 dias úteis · Correios, 5 a 12 dias no destino');

        $cache = [
            'valor' => (float) self::get('frete_padrao', '18.90'),
            'nome' => (string) self::get('frete_nome', 'Despacho do ateliê'),
            'prazo' => (string) self::get('frete_prazo', 'Sai em até 3 dias úteis · Correios, 5 a 12 dias no destino'),
        ];

        return $cache;
    }

    public static function setFrete(float $valor, string $nome, string $prazo): void
    {
        $valor = max(0, round($valor, 2));
        $nome = mb_substr(trim($nome) !== '' ? trim($nome) : 'Despacho do ateliê', 0, 80);
        $prazo = mb_substr(trim($prazo) !== '' ? trim($prazo) : 'Sai em até 3 dias úteis · Correios, 5 a 12 dias no destino', 0, 180);
        self::set('frete_padrao', number_format($valor, 2, '.', ''));
        self::set('frete_nome', $nome);
        self::set('frete_prazo', $prazo);
    }

    public const REDES = ['instagram' => 'Instagram', 'tiktok' => 'TikTok', 'youtube' => 'YouTube', 'facebook' => 'Facebook'];

    /**
     * @return array{frete_gratis: float, desconto_pix: float, parcelas: int, brinde_acima: float, brinde_texto: string,
     *   avisos: list<string>, atendimento: string, redes: array<string, string>, faq: list<array{p: string, r: string}>|null}
     */
    public static function vitrine(): array
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }
        self::garantir('redes_instagram', 'https://instagram.com/elomiah');

        $redes = [];
        foreach (array_keys(self::REDES) as $rede) {
            $url = trim((string) self::get('redes_' . $rede, ''));
            if ($url !== '') {
                $redes[$rede] = $url;
            }
        }
        $faq = self::get('faq_loja');
        $faq = $faq === null ? null : array_values(array_filter(
            (array) json_decode((string) $faq, true),
            static fn ($i) => is_array($i) && ($i['p'] ?? '') !== '' && ($i['r'] ?? '') !== ''
        ));

        return $cache = [
            'frete_gratis' => max(0, (float) self::get('frete_gratis_acima', '0')),
            'desconto_pix' => min(30, max(0, (float) self::get('desconto_pix', '0'))),
            'parcelas' => min(6, max(0, (int) self::get('parcelas_sem_juros', '0'))),
            'brinde_acima' => max(0, (float) self::get('brinde_acima', '0')),
            'brinde_texto' => trim((string) self::get('brinde_texto', '')),
            'avisos' => self::linhas((string) self::get('faixa_avisos', ''), 3, 90),
            'atendimento' => trim((string) self::get('atendimento_horario', '')),
            'redes' => $redes,
            'faq' => $faq,
        ];
    }

    /** @param array<string, mixed> $d */
    public static function setVitrine(array $d): void
    {
        $dinheiro = static fn ($v) => number_format(max(0, round((float) str_replace(',', '.', (string) $v), 2)), 2, '.', '');
        self::set('frete_gratis_acima', $dinheiro($d['frete_gratis_acima'] ?? 0));
        self::set('desconto_pix', (string) min(30, max(0, round((float) str_replace(',', '.', (string) ($d['desconto_pix'] ?? 0)), 1))));
        self::set('parcelas_sem_juros', (string) min(6, max(0, (int) ($d['parcelas_sem_juros'] ?? 0))));
        self::set('brinde_acima', $dinheiro($d['brinde_acima'] ?? 0));
        self::set('brinde_texto', mb_substr(trim((string) ($d['brinde_texto'] ?? '')), 0, 80));
        self::set('faixa_avisos', implode("\n", self::linhas((string) ($d['faixa_avisos'] ?? ''), 3, 90)));
        self::set('atendimento_horario', mb_substr(trim((string) ($d['atendimento_horario'] ?? '')), 0, 120));
        foreach (array_keys(self::REDES) as $rede) {
            $url = trim((string) ($d['redes_' . $rede] ?? ''));
            self::set('redes_' . $rede, preg_match('#^https://[^\s"<>]+$#', $url) ? mb_substr($url, 0, 200) : '');
        }
        $faq = [];
        foreach ((array) ($d['faq_p'] ?? []) as $i => $p) {
            $p = mb_substr(trim((string) $p), 0, 160);
            $r = mb_substr(trim((string) (($d['faq_r'] ?? [])[$i] ?? '')), 0, 800);
            if ($p !== '' && $r !== '') {
                $faq[] = ['p' => $p, 'r' => $r];
            }
        }
        self::set('faq_loja', (string) json_encode(array_slice($faq, 0, 8), JSON_UNESCAPED_UNICODE));
    }

    /** @return list<string> */
    private static function linhas(string $texto, int $max, int $tamanho): array
    {
        $linhas = array_map(static fn ($l) => mb_substr(trim($l), 0, $tamanho), preg_split('/\R/', $texto) ?: []);
        return array_slice(array_values(array_filter($linhas, static fn ($l) => $l !== '')), 0, $max);
    }

    public static function pix(): array
    {
        $cfg = config('app');
        $chave = trim((string) self::get('pix_chave', ''));
        $nome = trim((string) self::get('pix_nome', ''));
        $cidade = trim((string) self::get('pix_cidade', ''));

        return [
            'chave' => $chave !== '' ? $chave : trim((string) ($cfg['pix_chave'] ?? '')),
            'nome' => $nome !== '' ? $nome : trim((string) ($cfg['pix_nome'] ?? 'ELOMIAH')),
            'cidade' => $cidade !== '' ? $cidade : trim((string) ($cfg['pix_cidade'] ?? 'SAO PAULO')),
        ];
    }

    public static function setPix(string $chave, string $nome, string $cidade): void
    {
        $chave = trim($chave);
        $nome = mb_substr(trim($nome) !== '' ? trim($nome) : 'ELOMIAH', 0, 25);
        $cidade = mb_substr(trim($cidade) !== '' ? trim($cidade) : 'SAO PAULO', 0, 15);
        if ($chave !== '') {
            self::set('pix_chave', $chave);
        }
        self::set('pix_nome', $nome);
        self::set('pix_cidade', $cidade);
    }

    /** @return array{razao: string, cnpj: string, ie: string, endereco: string} */
    public static function loja(): array
    {
        $cfg = config('app');
        $razao = trim((string) self::get('loja_razao', ''));
        $cnpj = trim((string) self::get('loja_cnpj', ''));
        $ie = trim((string) self::get('loja_ie', ''));
        $endereco = trim((string) self::get('loja_endereco', ''));

        return [
            'razao' => $razao !== '' ? $razao : trim((string) ($cfg['loja_razao'] ?? 'Elomiah')),
            'cnpj' => $cnpj !== '' ? $cnpj : trim((string) ($cfg['loja_cnpj'] ?? '')),
            'ie' => $ie !== '' ? $ie : trim((string) ($cfg['loja_ie'] ?? '')),
            'endereco' => $endereco !== '' ? $endereco : trim((string) ($cfg['loja_endereco'] ?? '')),
        ];
    }

    public static function setLoja(string $razao, string $cnpj, string $ie, string $endereco): void
    {
        self::set('loja_razao', mb_substr(trim($razao) !== '' ? trim($razao) : 'Elomiah', 0, 120));
        self::set('loja_cnpj', mb_substr(trim($cnpj), 0, 32));
        self::set('loja_ie', mb_substr(trim($ie), 0, 32));
        self::set('loja_endereco', mb_substr(trim($endereco), 0, 180));
    }
}
