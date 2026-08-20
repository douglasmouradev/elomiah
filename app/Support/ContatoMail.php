<?php

declare(strict_types=1);

namespace App\Support;

final class ContatoMail
{
    public static function aviso(array $mensagem): void
    {
        $admin = Mail::admin();
        if ($admin === '') {
            return;
        }

        $nome = (string) ($mensagem['nome'] ?? '');
        $email = (string) ($mensagem['email'] ?? '');
        $assunto = (string) ($mensagem['assunto'] ?? 'Contato pelo site');
        $texto = nl2br(e((string) ($mensagem['mensagem'] ?? '')));
        $html = '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"></head>'
            . '<body style="margin:0;padding:0;background:#FDFBF6;color:#1B4332">'
            . '<div style="max-width:560px;margin:0 auto;padding:40px 24px;font-family:Georgia,serif">'
            . '<p style="letter-spacing:.38em;font-size:11px;color:#C9A24B">ELOMIAH</p>'
            . '<h1 style="font-weight:400;font-size:28px">Mensagem no ateliê</h1>'
            . '<p style="line-height:1.7">' . e($nome) . ' · ' . e($email) . '</p>'
            . '<p style="line-height:1.7"><strong>' . e($assunto) . '</strong></p>'
            . '<p style="line-height:1.7">' . $texto . '</p>'
            . '<p><a href="' . e(url('/admin/contato')) . '" style="display:inline-block;padding:12px 22px;background:#1B4332;color:#FDFBF6;text-decoration:none;letter-spacing:.16em;text-transform:uppercase;font-size:11px">Abrir no painel</a></p>'
            . '</div></body></html>';

        Mail::send($admin, 'Contato · ' . $assunto, $html);
    }
}
