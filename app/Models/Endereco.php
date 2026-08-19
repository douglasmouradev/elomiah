<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

final class Endereco extends Model
{
    protected static string $table = 'enderecos';

    public static function ultimoDoCliente(int $usuarioId): ?array
    {
        if ($usuarioId < 1) {
            return null;
        }

        return Database::fetch(
            'SELECT * FROM enderecos WHERE usuario_id = :id ORDER BY id DESC LIMIT 1',
            ['id' => $usuarioId]
        );
    }

    /** @param array<string, mixed> $dados */
    public static function salvarDoCliente(int $usuarioId, array $dados): int
    {
        $cep = preg_replace('/\D+/', '', (string) ($dados['cep'] ?? '')) ?? '';

        return self::create([
            'usuario_id' => $usuarioId,
            'cep' => $cep,
            'logradouro' => (string) ($dados['logradouro'] ?? ''),
            'numero' => (string) ($dados['numero'] ?? ''),
            'complemento' => ($dados['complemento'] ?? '') !== '' ? (string) $dados['complemento'] : null,
            'bairro' => (string) ($dados['bairro'] ?? ''),
            'cidade' => (string) ($dados['cidade'] ?? ''),
            'estado' => strtoupper((string) ($dados['estado'] ?? '')),
        ]);
    }
}
