<?php

declare(strict_types=1);

use App\Modules\Archivos\Infrastructure\Providers\ArchivosServiceProvider;
use App\Modules\Expedientes\Infrastructure\Providers\ExpedienteServiceProvider;
use App\Modules\ITSE\Infrastructure\Providers\ItseServiceProvider;
use App\Modules\Licencias\Infrastructure\Providers\LicenciasServiceProvider;
use App\Modules\Personas\Infrastructure\Providers\PersonasServiceProvider;
use App\Modules\Pide\Infrastructure\Providers\PideServiceProvider;
use App\Modules\Seguridad\Infrastructure\Providers\SeguridadServiceProvider;
use App\Providers\AppServiceProvider;

return [
    PersonasServiceProvider::class,
    AppServiceProvider::class,
    LicenciasServiceProvider::class,
    ItseServiceProvider::class,
    ExpedienteServiceProvider::class,
    PideServiceProvider::class,
    ArchivosServiceProvider::class,
    SeguridadServiceProvider::class,
];
