<?php

declare(strict_types=1);

use App\Modules\Estados\Infrastructure\Providers\EstadosServiceProvider;
use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
    EstadosServiceProvider::class,
    App\Modules\Licencias\Infrastructure\Providers\LicenciasServiceProvider::class,
        App\Modules\TipoLicencia\Infrastructure\Providers\TipoLicenciaServiceProvider::class,
];
