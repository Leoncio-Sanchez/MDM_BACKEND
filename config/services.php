<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'reniec' => [
        'url' => env('RENIEC_URL', 'https://ws2.pide.gob.pe/Rest/RENIEC/Consultar'),
        'dni_usuario' => env('RENIEC_NUDNIUSUARIO', ''),
        'ruc_usuario' => env('RENIEC_NURUCUSUARIO', ''),
        'password' => env('RENIEC_PASSWORD', ''),
        'timeout' => env('RENIEC_TIMEOUT', 10),
    ],

    'sunat' => [
        'url' => env('SUNAT_URL', 'https://ws3.pide.gob.pe/Rest/Sunat/DatosPrincipales'),
    ],
];
