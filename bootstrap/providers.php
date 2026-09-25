<?php

declare(strict_types=1);

use App\Modules\Seguridad\Infrastructure\Providers\SeguridadServiceProvider;
use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
    SeguridadServiceProvider::class,
];
