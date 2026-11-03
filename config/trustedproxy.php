<?php

declare(strict_types=1);

return [

    /*
     * Proxies de los que se aceptan las cabeceras X-Forwarded-*: IP o subred, separadas por comas.
     * En producción, la subred de la red "web" de nginx-proxy. Vacío = no se confía en ninguno.
     * Las cabeceras que se aceptan están en bootstrap/app.php.
     */
    'proxies' => env('TRUSTED_PROXIES') ?: null,

];
