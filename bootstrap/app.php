<?php
/**
 * Application autoloader + bootstrap entry point.
 * Include this file at the top of every page.
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';

// Simple PSR-4 style autoloader for app/ directory
spl_autoload_register(function (string $class): void {
    $base = APP_ROOT . '/app/';
    $map  = [
        'Models\\'      => $base . 'Models/',
        'Services\\'    => $base . 'Services/',
        'Controllers\\' => $base . 'Controllers/',
    ];
    foreach ($map as $prefix => $dir) {
        if (str_starts_with($class, $prefix)) {
            $file = $dir . substr($class, strlen($prefix)) . '.php';
            if (is_file($file)) {
                require_once $file;
                return;
            }
        }
    }
    // Fallback: flat class name (Database, Auth, etc.)
    $file = $base . $class . '.php';
    if (is_file($file)) require_once $file;
});
