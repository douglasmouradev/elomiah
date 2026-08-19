<?php

declare(strict_types=1);

namespace App\Core;

final class Validator
{
    public static function make(array $data, array $rules): array
    {
        $errors = [];
        foreach ($rules as $field => $ruleStr) {
            $value = $data[$field] ?? null;
            foreach (explode('|', $ruleStr) as $rule) {
                $param = null;
                if (str_contains($rule, ':')) {
                    [$rule, $param] = explode(':', $rule, 2);
                }
                $message = self::check($field, $value, $rule, $param, $data);
                if ($message) {
                    $errors[$field] = $message;
                    break;
                }
            }
        }
        return $errors;
    }

    private static function check(string $field, mixed $value, string $rule, ?string $param, array $data): ?string
    {
        $label = str_replace('_', ' ', $field);
        $empty = $value === null || $value === '';

        return match ($rule) {
            'required' => $empty ? 'O campo ' . $label . ' é obrigatório.' : null,
            'email' => (!$empty && !filter_var((string) $value, FILTER_VALIDATE_EMAIL)) ? 'Informe um e-mail válido.' : null,
            'min' => (mb_strlen((string) $value) < (int) $param) ? 'O campo ' . $label . ' é muito curto.' : null,
            'max' => (mb_strlen((string) $value) > (int) $param) ? 'O campo ' . $label . ' é muito longo.' : null,
            'numeric' => (!$empty && !is_numeric($value)) ? 'Informe um valor numérico.' : null,
            'cep' => (!$empty && !preg_match('/^\d{8}$/', preg_replace('/\D+/', '', (string) $value) ?? '')) ? 'CEP inválido.' : null,
            'confirmed' => ((string) $value !== (string) ($data[$param ?? $field . '_confirmation'] ?? '')) ? 'A confirmação não confere.' : null,
            'in' => (!in_array((string) $value, explode(',', (string) $param), true)) ? 'Valor inválido.' : null,
            default => null,
        };
    }

    public static function sanitize(array $data): array
    {
        $clean = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $clean[$key] = self::sanitize($value);
                continue;
            }
            if (is_string($value)) {
                $clean[$key] = trim(strip_tags($value));
            } else {
                $clean[$key] = $value;
            }
        }
        return $clean;
    }
}
