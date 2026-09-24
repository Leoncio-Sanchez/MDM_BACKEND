<?php

declare(strict_types=1);

use App\Modules\Archivos\Infrastructure\Providers\ArchivosServiceProvider;
use App\Modules\Estados\Infrastructure\Providers\EstadosServiceProvider;
use App\Modules\Expedientes\Infrastructure\Providers\ExpedienteServiceProvider;
use App\Modules\Giros\Infrastructure\Providers\GirosServiceProvider;
use App\Modules\ITSE\Infrastructure\Providers\ItseServiceProvider;
use App\Modules\Licencias\Infrastructure\Providers\LicenciasServiceProvider;
use App\Modules\NivelRiesgo\Infrastructure\Providers\NivelRiesgoServiceProvider;
use App\Modules\Personas\Infrastructure\Providers\PersonasServiceProvider;
use App\Modules\Pide\Infrastructure\Providers\PideServiceProvider;
use App\Modules\Seguridad\Infrastructure\Providers\SeguridadServiceProvider;
use App\Modules\TipoDocumentoIdentidad\Infrastructure\Providers\TipoDocumentoIdentidadServiceProvider;
use App\Modules\TipoLicencia\Infrastructure\Providers\TipoLicenciaServiceProvider;
use App\Modules\TipoProcedimientoTupa\Infrastructure\Providers\TipoProcedimientoTupaServiceProvider;
use App\Modules\Inspectores\Infrastructure\Providers\InspectoresServiceProvider;
use App\Modules\UnidadesOrganicas\Infrastructure\Providers\UnidadesOrganicasServiceProvider;
use App\Modules\Usuarios\Infrastructure\Providers\UsuariosServiceProvider;
use App\Modules\Zonificacion\Infrastructure\Providers\ZonificacionServiceProvider;
use App\Providers\AppServiceProvider;

return [
    TipoProcedimientoTupaServiceProvider::class,
    PersonasServiceProvider::class,
    GirosServiceProvider::class,
    TipoDocumentoIdentidadServiceProvider::class,
    ZonificacionServiceProvider::class,
    NivelRiesgoServiceProvider::class,
    AppServiceProvider::class,
    EstadosServiceProvider::class,
    LicenciasServiceProvider::class,
    ItseServiceProvider::class,
    TipoLicenciaServiceProvider::class,
    ExpedienteServiceProvider::class,
    PideServiceProvider::class,
    ArchivosServiceProvider::class,
    SeguridadServiceProvider::class,
    UsuariosServiceProvider::class,
    InspectoresServiceProvider::class,
    UnidadesOrganicasServiceProvider::class,
];
