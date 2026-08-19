<?php

declare(strict_types=1);

namespace App\Core;

abstract class Model
{
    protected static string $table = '';
    protected static string $primaryKey = 'id';

    public static function find(int $id): ?array
    {
        return Database::fetch(
            'SELECT * FROM `' . static::$table . '` WHERE `' . static::$primaryKey . '` = :id LIMIT 1',
            ['id' => $id]
        );
    }

    public static function all(string $order = 'id DESC'): array
    {
        return Database::fetchAll('SELECT * FROM `' . static::$table . '` ORDER BY ' . self::safeOrder($order));
    }

    public static function where(string $column, mixed $value, string $order = 'id DESC'): array
    {
        self::assertColumn($column);
        return Database::fetchAll(
            'SELECT * FROM `' . static::$table . '` WHERE `' . $column . '` = :v ORDER BY ' . self::safeOrder($order),
            ['v' => $value]
        );
    }

    public static function firstWhere(string $column, mixed $value): ?array
    {
        self::assertColumn($column);
        return Database::fetch(
            'SELECT * FROM `' . static::$table . '` WHERE `' . $column . '` = :v LIMIT 1',
            ['v' => $value]
        );
    }

    public static function create(array $data): int
    {
        $data['created_at'] = $data['created_at'] ?? now();
        $data['updated_at'] = $data['updated_at'] ?? now();
        return Database::insert(static::$table, $data);
    }

    public static function updateById(int $id, array $data): int
    {
        $data['updated_at'] = now();
        return Database::update(static::$table, $data, static::$primaryKey . ' = :_id', ['_id' => $id]);
    }

    public static function deleteById(int $id): int
    {
        return Database::query(
            'DELETE FROM `' . static::$table . '` WHERE `' . static::$primaryKey . '` = :id',
            ['id' => $id]
        )->rowCount();
    }

    public static function count(string $where = '1=1', array $params = []): int
    {
        $row = Database::fetch('SELECT COUNT(*) AS total FROM `' . static::$table . '` WHERE ' . $where, $params);
        return (int) ($row['total'] ?? 0);
    }

    private static function assertColumn(string $column): void
    {
        if (!preg_match('/^[a-z_][a-z0-9_]*$/i', $column)) {
            throw new \RuntimeException('Coluna inválida.');
        }
    }

    private static function safeOrder(string $order): string
    {
        if (!preg_match('/^[a-z0-9_]+(\s+(ASC|DESC))?(,\s*[a-z0-9_]+(\s+(ASC|DESC))?)*$/i', $order)) {
            return 'id DESC';
        }
        return $order;
    }
}
