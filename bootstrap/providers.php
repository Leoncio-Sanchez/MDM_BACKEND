<?php

declare(strict_types=1);

use App\Modules\Archivos\Infrastructure\Providers\ArchivosServiceProvider;
use App\Modules\Expedientes\Infrastructure\Providers\ExpedienteServiceProvider;
use App\Modules\Inspectores\Infrastructure\Providers\InspectoresServiceProvider;
use App\Modules\ITSE\Infrastructure\Providers\ItseServiceProvider;
use App\Modules\Licencias\Infrastructure\Providers\LicenciasServiceProvider;
use App\Modules\Personas\Infrastructure\Providers\PersonasServiceProvider;
use App\Modules\Pide\Infrastructure\Providers\PideServiceProvider;
use App\Modules\Seguridad\Infrastructure\Providers\SeguridadServiceProvider;
use App\Modules\UnidadesOrganicas\Infrastructure\Providers\UnidadesOrganicasServiceProvider;
use App\Modules\Usuarios\Infrastructure\Providers\UsuariosServiceProvider;
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
    UsuariosServiceProvider::class,
    InspectoresServiceProvider::class,
    UnidadesOrganicasServiceProvider::class,
];
