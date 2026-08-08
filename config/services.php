<?php

return [
    'inegi' => [
        'base_url' => env('INEGI_BASE_URL', 'https://gaia.inegi.org.mx/wscatgeo/v2/'),
        'connect_timeout' => (int) env('INEGI_CONNECT_TIMEOUT', 3),
        'timeout' => (int) env('INEGI_TIMEOUT', 10),
        'retries' => (int) env('INEGI_RETRIES', 3),
        'retry_sleep_ms' => (int) env('INEGI_RETRY_SLEEP_MS', 250),
    ],
];
