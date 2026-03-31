<?php
declare(strict_types=1);

function env(string $key, mixed $default = null): mixed 
{
    $value = getenv($key);

    if ($value === false) {
        return $default;
    }

    return $value;
}

$config = [
    'db' => [
        'host'     => env('DB_HOST', 'db'), 
        'port'     => env('DB_PORT', '3306'),
        'database' => env('DB_NAME', 'tecnofit_db'),
        'user'     => env('DB_USER', 'root'),
        'pass'     => env('DB_PASS', 'root'),
        'charset'  => 'utf8mb4',
    ],
    'api' => [
        'display_errors' => env('APP_DEBUG', 'true') === 'true',
    ]
];

error_reporting($config['api']['display_errors'] ? E_ALL : 0);
ini_set('display_errors', $config['api']['display_errors'] ? '1' : '0');