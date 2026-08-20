<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use PDOStatement;
use RuntimeException;

final class Database
{
    private static ?PDO $pdo = null;

    public static function connection(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $cfg = config('database');
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $cfg['host'],
            $cfg['port'],
            $cfg['name'],
            $cfg['charset']
        );

        try {
            self::$pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            $hint = 'Não foi possível conectar ao banco de dados. Verifique DB_HOST, DB_USER e DB_PASS no .env e execute php database/install.php.';
            if (config('app')['debug'] ?? false) {
                $hint .= ' Detalhe: ' . $e->getMessage();
            }
            throw new RuntimeException($hint, (int) $e->getCode(), $e);
        }

        return self::$pdo;
    }

    public static function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::connection()->prepare($sql);
        foreach ($params as $key => $value) {
            $param = is_int($key) ? $key + 1 : (str_starts_with((string) $key, ':') ? $key : ':' . $key);
            $type = match (true) {
                is_int($value) => PDO::PARAM_INT,
                is_bool($value) => PDO::PARAM_BOOL,
                $value === null => PDO::PARAM_NULL,
                default => PDO::PARAM_STR,
            };
            $stmt->bindValue($param, $value, $type);
        }
        $stmt->execute();
        return $stmt;
    }

    public static function fetch(string $sql, array $params = []): ?array
    {
        $row = self::query($sql, $params)->fetch();
        return $row === false ? null : $row;
    }

    public static function fetchAll(string $sql, array $params = []): array
    {
        return self::query($sql, $params)->fetchAll();
    }

    public static function insert(string $table, array $data): int
    {
        self::assertTable($table);
        $columns = array_keys($data);
        foreach ($columns as $column) {
            self::assertIdentifier($column);
        }
        $fields = implode(', ', array_map(static fn ($c) => "`{$c}`", $columns));
        $placeholders = implode(', ', array_map(static fn ($c) => ':' . $c, $columns));
        self::query("INSERT INTO `{$table}` ({$fields}) VALUES ({$placeholders})", $data);
        return (int) self::connection()->lastInsertId();
    }

    public static function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        self::assertTable($table);
        $sets = [];
        foreach (array_keys($data) as $column) {
            self::assertIdentifier($column);
            $sets[] = "`{$column}` = :{$column}";
        }
        $sql = "UPDATE `{$table}` SET " . implode(', ', $sets) . " WHERE {$where}";
        return self::query($sql, array_merge($data, $whereParams))->rowCount();
    }

    public static function lastInsertId(): int
    {
        return (int) self::connection()->lastInsertId();
    }

    private static function assertTable(string $table): void
    {
        $allowed = [
            'usuarios', 'categorias', 'produtos', 'imagens_produto', 'pedidos', 'itens_pedido',
            'depoimentos', 'enderecos', 'cursos', 'modulos_curso', 'faqs_curso', 'logs_admin',
            'consentimentos_lgpd', 'solicitacoes_lgpd', 'visitantes', 'configuracoes',
            'mensagens_contato', 'rate_limits', 'notificacoes', 'recuperacao_senhas',
        ];
        if (!in_array($table, $allowed, true)) {
            throw new RuntimeException('Tabela não permitida.');
        }
    }

    private static function assertIdentifier(string $name): void
    {
        if (!preg_match('/^[a-z_][a-z0-9_]*$/i', $name)) {
            throw new RuntimeException('Identificador SQL inválido.');
        }
    }
}
