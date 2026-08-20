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
