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

    public static function images(array $files, string $folder): array
    {
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

    public static function one(array $file, string $folder): ?string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return null;
        }

        $max = ((int) (config('app')['upload_max_mb'] ?? 4)) * 1024 * 1024;
        if (($file['size'] ?? 0) > $max) {
            return null;
        }

        $tmp = $file['tmp_name'] ?? '';
        if (!is_uploaded_file($tmp)) {
            return null;
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmp);
        if (!isset(self::ALLOWED[$mime])) {
            return null;
        }

        $ext = self::ALLOWED[$mime];
        $name = bin2hex(random_bytes(16)) . '.' . $ext;
        $dir = PUBLIC_PATH . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $folder;
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $dest = $dir . DIRECTORY_SEPARATOR . $name;
        if (!move_uploaded_file($tmp, $dest)) {
            return null;
        }

        return 'uploads/' . $folder . '/' . $name;
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
