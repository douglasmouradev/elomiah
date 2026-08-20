<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Configuracao;

/**
 * Payload Pix (EMV / copia e cola) para a chave da Elomiah.
 */
final class Pix
{
    public static function chave(): string
    {
        return Configuracao::pix()['chave'];
    }

    public static function configurado(): bool
    {
        return self::chave() !== '';
    }

    public static function copiaECola(string $txid, float $valor): string
    {
        $chave = self::chave();
        $pix = Configuracao::pix();
        $nome = self::ascii($pix['nome'], 25);
        $cidade = self::ascii($pix['cidade'], 15);
        $txid = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $txid) ?: 'ELOMIAH', 0, 25));
        $valorStr = number_format(max(0, $valor), 2, '.', '');

        $merchant = self::tlv('00', 'br.gov.bcb.pix') . self::tlv('01', $chave);
        $adicional = self::tlv('05', $txid);

        $corpo = '000201'
            . '010212'
            . self::tlv('26', $merchant)
            . '52040000'
            . '5303986'
            . self::tlv('54', $valorStr)
            . '5802BR'
            . self::tlv('59', $nome)
            . self::tlv('60', $cidade)
            . self::tlv('62', $adicional)
            . '6304';

        return $corpo . self::crc16($corpo);
    }

    /**
     * @return array{banco:string, codigo:string, agencia:string, conta:string}
     */
    public static function contaRecebimento(): array
    {
        $cfg = config('app');
        return [
            'banco' => (string) ($cfg['banco_nome'] ?? 'Nu Pagamentos S.A.'),
            'codigo' => (string) ($cfg['banco_codigo'] ?? '0260'),
            'agencia' => (string) ($cfg['banco_agencia'] ?? '0001'),
            'conta' => (string) ($cfg['banco_conta'] ?? ''),
        ];
    }

    private static function tlv(string $id, string $value): string
    {
        return $id . str_pad((string) strlen($value), 2, '0', STR_PAD_LEFT) . $value;
    }

    private static function ascii(string $value, int $max): string
    {
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
        $value = strtoupper((string) preg_replace('/[^A-Za-z0-9 ]/', '', $value));
        $value = trim($value);
        return substr($value !== '' ? $value : 'ELOMIAH', 0, $max);
    }

    private static function crc16(string $payload): string
    {
        $crc = 0xFFFF;
        $len = strlen($payload);
        for ($i = 0; $i < $len; $i++) {
            $crc ^= ord($payload[$i]) << 8;
            for ($j = 0; $j < 8; $j++) {
                if ($crc & 0x8000) {
                    $crc = ($crc << 1) ^ 0x1021;
                } else {
                    $crc <<= 1;
                }
                $crc &= 0xFFFF;
            }
        }
        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }
}
