<?php

declare(strict_types=1);

/**
 * Orígenes permitidos: CORS_ALLOWED_ORIGINS, separados por comas. Sin la variable no se permite
 * ningún otro origen, salvo el frontend de desarrollo en local. "*" se descarta a propósito:
 * la API maneja datos de ciudadanos y no debe poder abrirse a cualquier sitio desde el .env.
 */
$origenes = (string) env('CORS_ALLOWED_ORIGINS', env('APP_ENV') === 'local' ? 'http://localhost:5173' : '');

return [

    'paths' => ['api/*'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    'allowed_origins' => array_values(array_filter(
        array_map(trim(...), explode(',', $origenes)),
        fn (string $origen): bool => $origen !== '' && ! str_contains($origen, '*'),
    )),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Accept', 'Authorization', 'Content-Type', 'X-Requested-With'],

    // El frontend lee el nombre del archivo y el código del informe al descargar
    'exposed_headers' => ['Content-Disposition', 'X-Informe-Codigo', 'X-Informe-Reimpresion'],

    'max_age' => 3600,

    'supports_credentials' => false,

];
