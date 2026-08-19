<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Envio de e-mail transacional.
 * Com MAIL_HOST usa SMTP; senão tenta mail() e registra em storage/logs/mail.log.
 */
final class Mail
{
    public static function send(string $to, string $subject, string $html, ?string $text = null): bool
    {
        $to = strtolower(trim($to));
        if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $text ??= trim(html_entity_decode(strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], "\n", $html)), ENT_QUOTES, 'UTF-8'));

        try {
            $ok = self::host() !== ''
                ? self::smtp($to, $subject, $html, $text)
                : self::phpMail($to, $subject, $html);
            self::log($to, $subject, $ok ? 'enviado' : 'falhou');
            return $ok;
        } catch (\Throwable $e) {
            self::log($to, $subject, 'erro: ' . $e->getMessage());
            return false;
        }
    }

    public static function admin(): string
    {
        $email = trim((string) env('MAIL_ADMIN', 'admin@elomiah.com'));
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : '';
    }

    public static function fromEmail(): string
    {
        $from = trim((string) env('MAIL_FROM', env('MAIL_USER', 'olivia.t@example.org')));
        return filter_var($from, FILTER_VALIDATE_EMAIL) ? $from : 'olivia.t@example.org';
    }

    public static function fromName(): string
    {
        $name = trim((string) env('MAIL_FROM_NAME', 'Elomiah'));
        return $name !== '' ? $name : 'Elomiah';
    }

    private static function host(): string
    {
        return trim((string) env('MAIL_HOST', ''));
    }

    private static function phpMail(string $to, string $subject, string $html): bool
    {
        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . self::headerMailbox(),
            'Reply-To: ' . self::fromEmail(),
            'X-Mailer: Elomiah',
        ];

        return @mail($to, self::encodeHeader($subject), $html, implode("\r\n", $headers));
    }

    private static function smtp(string $to, string $subject, string $html, string $text): bool
    {
        $host = self::host();
        $port = (int) env('MAIL_PORT', 587);
        $user = (string) env('MAIL_USER', '');
        $pass = (string) env('MAIL_PASS', '');
        $enc = strtolower((string) env('MAIL_ENCRYPTION', $port === 465 ? 'ssl' : 'tls'));
        $verify = env('MAIL_VERIFY_PEER', '1') !== '0';

        $remote = ($enc === 'ssl' ? 'ssl://' : 'tcp://') . $host . ':' . ($port > 0 ? $port : 587);
        $ctx = stream_context_create([
            'ssl' => [
                'verify_peer' => $verify,
                'verify_peer_name' => $verify,
                'allow_self_signed' => !$verify,
            ],
        ]);

        $fp = @stream_socket_client($remote, $errno, $errstr, 20, STREAM_CLIENT_CONNECT, $ctx);
        if (!$fp) {
            throw new \RuntimeException('SMTP indisponível: ' . $errstr);
        }
        stream_set_timeout($fp, 20);

        self::expect($fp, 220);
        $ehlo = self::ehloHost();
        self::command($fp, 'EHLO ' . $ehlo, [250]);

        if ($enc === 'tls') {
            self::command($fp, 'STARTTLS', [220]);
            if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                fclose($fp);
                throw new \RuntimeException('STARTTLS recusado.');
            }
            self::command($fp, 'EHLO ' . $ehlo, [250]);
        }

        if ($user !== '') {
            self::command($fp, 'AUTH LOGIN', [334]);
            self::command($fp, base64_encode($user), [334]);
            self::command($fp, base64_encode($pass), [235]);
        }

        self::command($fp, 'MAIL FROM:<' . self::fromEmail() . '>', [250]);
        self::command($fp, 'RCPT TO:<' . $to . '>', [250, 251]);
        self::command($fp, 'DATA', [354]);
        fwrite($fp, self::mime($to, $subject, $html, $text) . "\r\n.\r\n");
        self::expect($fp, 250);
        fwrite($fp, "QUIT\r\n");
        fclose($fp);

        return true;
    }

    private static function mime(string $to, string $subject, string $html, string $text): string
    {
        $boundary = 'elomiah_' . bin2hex(random_bytes(8));
        $lines = [
            'Date: ' . date('r'),
            'From: ' . self::headerMailbox(),
            'To: ' . $to,
            'Subject: ' . self::encodeHeader($subject),
            'MIME-Version: 1.0',
            'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
            '',
            '--' . $boundary,
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: base64',
            '',
            trim(chunk_split(base64_encode($text))),
            '--' . $boundary,
            'Content-Type: text/html; charset=UTF-8',
            'Content-Transfer-Encoding: base64',
            '',
            trim(chunk_split(base64_encode($html))),
            '--' . $boundary . '--',
        ];

        return implode("\r\n", $lines);
    }

    private static function headerMailbox(): string
    {
        return self::encodeHeader(self::fromName()) . ' <' . self::fromEmail() . '>';
    }

    private static function encodeHeader(string $value): string
    {
        if (preg_match('/^[\x20-\x7E]+$/', $value)) {
            return $value;
        }

        return '=?UTF-8?B?' . base64_encode($value) . '?=';
    }

    private static function ehloHost(): string
    {
        $host = parse_url((string) (config('app')['url'] ?? ''), PHP_URL_HOST);
        return is_string($host) && $host !== '' ? $host : 'localhost';
    }

    /** @param resource $fp */
    private static function command($fp, string $cmd, array $ok): string
    {
        fwrite($fp, $cmd . "\r\n");
        return self::expect($fp, $ok);
    }

    /** @param resource $fp */
    private static function expect($fp, int|array $ok): string
    {
        $ok = (array) $ok;
        $reply = '';
        while (($line = fgets($fp, 1024)) !== false) {
            $reply .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }
        $code = (int) substr($reply, 0, 3);
        if (!in_array($code, $ok, true)) {
            throw new \RuntimeException('SMTP ' . $code . ': ' . trim($reply));
        }

        return $reply;
    }

    private static function log(string $to, string $subject, string $resultado): void
    {
        $dir = STORAGE_PATH . DIRECTORY_SEPARATOR . 'logs';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $line = sprintf("[%s] %s | %s | %s\n", date('c'), $resultado, $to, $subject);
        @file_put_contents($dir . DIRECTORY_SEPARATOR . 'mail.log', $line, FILE_APPEND);
        @file_put_contents($dir . DIRECTORY_SEPARATOR . 'app.log', $line, FILE_APPEND);
    }
}
