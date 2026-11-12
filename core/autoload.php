<?php
/**
 * Fallback PSR-4 autoloader, used ONLY if vendor/autoload.php does not exist
 * (i.e. you have not run `composer dump-autoload` yet).
 * It follows the same namespace -> folder mapping as composer.json.
 */
spl_autoload_register(function (string $class): void {
    $map = [
        'App\\'  => __DIR__ . '/../app/',
        'Core\\' => __DIR__ . '/',
    ];
    foreach ($map as $prefix => $baseDir) {
        if (strncmp($class, $prefix, strlen($prefix)) === 0) {
            $relative = substr($class, strlen($prefix));
            $file = $baseDir . str_replace('\\', '/', $relative) . '.php';
            if (is_file($file)) {
                require $file;
            }
            return;
        }
    }
});

require __DIR__ . '/helpers.php';
