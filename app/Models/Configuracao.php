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
}
