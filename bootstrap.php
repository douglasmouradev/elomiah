<?php

declare(strict_types=1);

/**
 * Bootstrap da aplicação Elomiah.
 */

define('BASE_PATH', dirname(__FILE__));
define('APP_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'app');
define('PUBLIC_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'public');
define('STORAGE_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'storage');
define('CONFIG_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'config');

$autoload = BASE_PATH . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
if (is_file($autoload)) {
    require $autoload;
} else {
    require APP_PATH . DIRECTORY_SEPARATOR . 'Helpers' . DIRECTORY_SEPARATOR . 'functions.php';

    spl_autoload_register(static function (string $class): void {
        $prefix = 'App\\';
        if (!str_starts_with($class, $prefix)) {
            return;
        }
        $relative = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, strlen($prefix)));
        $file = APP_PATH . DIRECTORY_SEPARATOR . $relative . '.php';
        if (is_file($file)) {
            require $file;
        }
    });
}

use App\Core\Env;

Env::load(BASE_PATH . DIRECTORY_SEPARATOR . '.env');

date_default_timezone_set('America/Sao_Paulo');

if (env('APP_DEBUG', 'false') !== 'true') {
    ini_set('display_errors', '0');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}
