<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');

(new App\Core\Application())->run();
