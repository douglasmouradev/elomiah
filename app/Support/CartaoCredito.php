<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Validação de cartão no checkout. Número e CVV nunca saem da requisição.
 */
final class CartaoCredito
{
    public static function somenteDigitos(string $value): string
    {
        return preg_replace('/\D+/', '', $value) ?? '';
    }

    public static function bandeira(string $digits): string
    {
        if (self::isElo($digits)) {
            return 'elo';
        }
        if (preg_match('/^3[47]/', $digits)) {
            return 'amex';
        }
        if (preg_match('/^4/', $digits)) {
            return 'visa';
        }
        if (preg_match('/^(5[1-5]|222[1-9]|22[3-9]\d|2[3-6]\d{2}|27[01]\d|2720)/', $digits)) {
            return 'mastercard';
        }
        if (preg_match('/^(606282|3841)/', $digits)) {
            return 'hipercard';
        }
        return 'cartao';
    }

    public static function bandeiraRotulo(string $bandeira): string
    {
        return match ($bandeira) {
            'visa', 'debvisa' => 'Visa',
            'master', 'mastercard', 'debmaster' => 'Mastercard',
            'elo' => 'Elo',
            'amex' => 'American Express',
            'hiper', 'hipercard' => 'Hipercard',
            'pix' => 'Pix',
            default => $bandeira !== '' ? ucfirst($bandeira) : 'Cartão',
        };
    }

    public static function luhn(string $digits): bool
    {
        if ($digits === '' || !ctype_digit($digits)) {
            return false;
        }
        $sum = 0;
        $alt = false;
        for ($i = strlen($digits) - 1; $i >= 0; $i--) {
            $n = (int) $digits[$i];
            if ($alt) {
                $n *= 2;
                if ($n > 9) {
                    $n -= 9;
                }
            }
            $sum += $n;
            $alt = !$alt;
        }
        return $sum % 10 === 0;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, string>
     */
    public static function validar(array $data, int $maxParcelas = 6): array
    {
        $errors = [];
        $numero = self::somenteDigitos((string) ($data['cartao_numero'] ?? ''));
        $titular = trim((string) ($data['cartao_titular'] ?? ''));
        $validade = (string) ($data['cartao_validade'] ?? '');
        $cvv = self::somenteDigitos((string) ($data['cartao_cvv'] ?? ''));
        $parcelas = (int) ($data['parcelas'] ?? 0);

        if (strlen($numero) < 13 || strlen($numero) > 19 || !self::luhn($numero)) {
            $errors['cartao_numero'] = 'Informe um número de cartão válido.';
        }
        if (mb_strlen($titular) < 3) {
            $errors['cartao_titular'] = 'Informe o nome impresso no cartão.';
        }
        if (!self::validadeOk($validade)) {
            $errors['cartao_validade'] = 'A validade do cartão está incorreta ou vencida.';
        }
        if (strlen($cvv) < 3 || strlen($cvv) > 4) {
            $errors['cartao_cvv'] = 'Informe o código de segurança (CVV).';
        }
        if ($parcelas < 1 || $parcelas > $maxParcelas) {
            $errors['parcelas'] = 'Escolha o número de parcelas.';
        }

        return $errors;
    }

    /**
     * @return array<int, string>
     */
    public static function parcelasOpcoes(float $total, int $max = 6): array
    {
        $opts = [];
        for ($n = 1; $n <= $max; $n++) {
            $valor = round($total / $n, 2);
            $opts[$n] = $n === 1
                ? '1x de ' . money($total) . ' à vista'
                : $n . 'x de ' . money($valor) . ' sem juros';
        }
        return $opts;
    }

    public static function validadeOk(string $validade): bool
    {
        $digits = self::somenteDigitos($validade);
        if (strlen($digits) !== 4) {
            return false;
        }
        $mes = (int) substr($digits, 0, 2);
        $ano = 2000 + (int) substr($digits, 2, 2);
        if ($mes < 1 || $mes > 12) {
            return false;
        }
        $limite = (int) sprintf('%04d%02d', $ano, $mes);
        $agora = (int) date('Ym');
        return $limite >= $agora;
    }

    private static function isElo(string $digits): bool
    {
        $prefixos = [
            '4011', '4312', '4389', '4514', '4576', '5041', '5066', '5067',
            '5090', '6277', '6362', '6363', '6504', '6505', '6509', '6516', '6550',
        ];
        foreach ($prefixos as $p) {
            if (str_starts_with($digits, $p)) {
                return true;
            }
        }
        return false;
    }
}
