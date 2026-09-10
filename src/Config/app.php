<?php

declare(strict_types=1);

use App\Core\Env;

return [
    'name'     => Env::get('APP_NAME', 'LifeLink'),
    'env'      => Env::get('APP_ENV', 'production'),
    'debug'    => Env::bool('APP_DEBUG', false),
    'url'      => rtrim(Env::get('APP_URL', 'http://localhost'), '/'),
    'timezone' => Env::get('APP_TIMEZONE', 'UTC'),

    'session' => [
        'name'     => Env::get('SESSION_NAME', 'lifelink_session'),
        'lifetime' => Env::int('SESSION_LIFETIME', 7200),
    ],

    'matching' => [
        'default_radius_km'       => Env::int('MATCH_DEFAULT_RADIUS_KM', 25),
        'max_donors_per_request'  => Env::int('MATCH_MAX_DONORS_PER_REQUEST', 25),
    ],

    'rate_limit' => [
        'login_max'    => Env::int('RATE_LIMIT_LOGIN_MAX', 5),
        'login_window' => Env::int('RATE_LIMIT_LOGIN_WINDOW', 300),
    ],
];
