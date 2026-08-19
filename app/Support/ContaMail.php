<?php

declare(strict_types=1);

namespace App\Support;

final class ContaMail
{
    public static function recuperar(string $email, string $token): void
    {
        $link = url('/recuperar-senha/' . $token);
        $html = '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"></head>'
            . '<body style="margin:0;padding:0;background:#FDFBF6;color:#1B4332">'
            . '<div style="max-width:560px;margin:0 auto;padding:40px 24px;font-family:Georgia,serif">'
            . '<p style="letter-spacing:.38em;font-size:11px;color:#C9A24B">ELOMIAH</p>'
            . '<h1 style="font-weight:400;font-size:28px">Redefinir a senha</h1>'
            . '<p style="line-height:1.7">Recebemos um pedido para abrir de novo o acesso desta conta. O link vale por uma hora.</p>'
            . '<p><a href="' . e($link) . '" style="display:inline-block;padding:12px 22px;background:#1B4332;color:#FDFBF6;text-decoration:none;letter-spacing:.16em;text-transform:uppercase;font-size:11px">Escolher nova senha</a></p>'
            . '<p style="font-size:13px;color:#5C6B61">Se você não pediu isso, ignore esta carta. A senha atual permanece.</p>'
            . '</div></body></html>';

        Mail::send($email, 'Redefinir a senha · Elomiah', $html);
    }
}
