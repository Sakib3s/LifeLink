<?php

declare(strict_types=1);

use App\Core\Env;

return [
    'name'     => Env::get('APP_NAME', 'LifeLink'),
    'env'      => Env::get('APP_ENV', 'production'),
    'debug'    => Env::bool('APP_DEBUG', false),
    'url'      => rtrim(Env::get('APP_URL', 'http://localhost'), '/'),
    'timezone' => Env::get('APP_TIMEZONE', 'UTC'),

    // Derived from APP_URL's path component so one setting drives everything.
    // Empty when the app lives at the domain root (e.g. the DDEV setup);
    // "/rtm/lifelink" when deployed under a subpath (e.g. Hostinger).
    'base_path' => rtrim((string) (parse_url(Env::get('APP_URL', 'http://localhost'), PHP_URL_PATH) ?: ''), '/'),

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

    // Meta WhatsApp Cloud API. Leave the token blank for local/dev work:
    // WhatsAppService then logs the message it would have sent instead of
    // calling the API, so the rest of the app works with no credentials.
    'whatsapp' => [
        'enabled'          => Env::bool('WHATSAPP_ENABLED', false),
        'api_version'      => Env::get('WHATSAPP_API_VERSION', 'v21.0'),
        'phone_number_id'  => Env::get('WHATSAPP_PHONE_NUMBER_ID'),
        'access_token'     => Env::get('WHATSAPP_ACCESS_TOKEN'),
        // Meta requires a pre-approved template to message a user outside a
        // 24h customer-initiated session. One generic template with a single
        // body placeholder covers every notification type we send.
        'template_name'    => Env::get('WHATSAPP_TEMPLATE_NAME', 'lifelink_alert'),
        'template_lang'    => Env::get('WHATSAPP_TEMPLATE_LANG', 'en'),
    ],
];
