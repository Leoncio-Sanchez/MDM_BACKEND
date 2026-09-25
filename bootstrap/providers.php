<?php

declare(strict_types=1);

use App\Modules\Personas\Infrastructure\Providers\PersonasServiceProvider;
use App\Modules\Pide\Infrastructure\Providers\PideServiceProvider;
use App\Modules\Seguridad\Infrastructure\Providers\SeguridadServiceProvider;
use App\Providers\AppServiceProvider;

return [
    PersonasServiceProvider::class,
    AppServiceProvider::class,
    PideServiceProvider::class,
    SeguridadServiceProvider::class,
];
