<?php

declare(strict_types=1);

use App\Core\Env;

return [
    'host'     => Env::get('DB_HOST', '127.0.0.1'),
    'port'     => Env::int('DB_PORT', 3306),
    'name'     => Env::get('DB_NAME', 'lifelink'),
    'user'     => Env::get('DB_USER', 'root'),
    'password' => Env::get('DB_PASSWORD', ''),
    'charset'  => Env::get('DB_CHARSET', 'utf8mb4'),
];
