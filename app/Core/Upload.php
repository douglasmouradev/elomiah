<?php

declare(strict_types=1);

namespace App\Core;

final class Upload
{
    private const ALLOWED = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    private const LADO_MAX = 1800;

    /** @var list<string> */
    private static array $recusadas = [];

    public static function images(array $files, string $folder): array
    {
        self::$recusadas = [];
        $saved = [];
        $normalized = self::normalize($files);
        foreach ($normalized as $file) {
            $path = self::one($file, $folder);
            if ($path) {
                $saved[] = $path;
            }
        }
        return $saved;
    }

    /** @return list<string> Nome e motivo das imagens que não entraram no último envio. */
    public static function recusadas(): array
    {
        return self::$recusadas;
    }

    public static function one(array $file, string $folder): ?string
    {
        $nome = (string) ($file['name'] ?? 'imagem');
        $erro = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($erro === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($erro === UPLOAD_ERR_INI_SIZE || $erro === UPLOAD_ERR_FORM_SIZE) {
            return self::recusar($nome, 'arquivo grande demais');
        }
        if ($erro !== UPLOAD_ERR_OK) {
            return self::recusar($nome, 'o envio não terminou');
        }

        $max = ((int) (config('app')['upload_max_mb'] ?? 4)) * 1024 * 1024;
        if (($file['size'] ?? 0) > $max) {
            return self::recusar($nome, 'passa de ' . (int) ($max / 1024 / 1024) . ' MB');
        }

        $tmp = $file['tmp_name'] ?? '';
        if (!is_uploaded_file($tmp)) {
            return self::recusar($nome, 'o envio não terminou');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmp);
        if (!isset(self::ALLOWED[$mime])) {
            return self::recusar($nome, 'use JPG, PNG ou WebP');
        }

        $dir = PUBLIC_PATH . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $folder;
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $base = bin2hex(random_bytes(16));

        $reduzida = self::reduzir($tmp, $mime, $dir . DIRECTORY_SEPARATOR . $base . '.webp');
        if ($reduzida) {
            return 'uploads/' . $folder . '/' . $base . '.webp';
        }

        $name = $base . '.' . self::ALLOWED[$mime];
        if (!move_uploaded_file($tmp, $dir . DIRECTORY_SEPARATOR . $name)) {
            return self::recusar($nome, 'não foi possível gravar no servidor');
        }

        return 'uploads/' . $folder . '/' . $name;
    }

    /** Reduz fotos grandes para no máximo LADO_MAX px e grava em WebP. */
    private static function reduzir(string $tmp, string $mime, string $dest): bool
    {
        if (!function_exists('imagewebp')) {
            return false;
        }
        $info = @getimagesize($tmp);
        if (!$info || max($info[0], $info[1]) <= self::LADO_MAX) {
            return false;
        }
        $src = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($tmp),
            'image/png' => @imagecreatefrompng($tmp),
            'image/webp' => @imagecreatefromwebp($tmp),
            default => false,
        };
        if (!$src) {
            return false;
        }
        if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
            $orient = (int) (@exif_read_data($tmp)['Orientation'] ?? 1);
            $src = match ($orient) {
                3 => imagerotate($src, 180, 0) ?: $src,
                6 => imagerotate($src, -90, 0) ?: $src,
                8 => imagerotate($src, 90, 0) ?: $src,
                default => $src,
            };
        }
        $w = imagesx($src);
        $h = imagesy($src);
        $escala = self::LADO_MAX / max($w, $h);
        $nw = (int) round($w * $escala);
        $nh = (int) round($h * $escala);
        $dst = imagecreatetruecolor($nw, $nh);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        $ok = imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
        imagedestroy($src);
        if (!$ok) {
            imagedestroy($dst);
            return false;
        }
        $ok = imagewebp($dst, $dest, 86);
        imagedestroy($dst);

        return $ok;
    }

    private static function recusar(string $nome, string $motivo): null
    {
        self::$recusadas[] = $nome . ' (' . $motivo . ')';
        return null;
    }

    private static function normalize(array $files): array
    {
        if (!isset($files['name'])) {
            return [];
        }
        if (!is_array($files['name'])) {
            return [$files];
        }
        $out = [];
        foreach ($files['name'] as $i => $name) {
            $out[] = [
                'name' => $name,
                'type' => $files['type'][$i] ?? '',
                'tmp_name' => $files['tmp_name'][$i] ?? '',
                'error' => $files['error'][$i] ?? UPLOAD_ERR_NO_FILE,
                'size' => $files['size'][$i] ?? 0,
            ];
        }
        return $out;
    }
}
