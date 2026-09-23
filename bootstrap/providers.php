<?php

declare(strict_types=1);

use App\Modules\Estados\Infrastructure\Providers\EstadosServiceProvider;
use App\Providers\AppServiceProvider;

return [
    App\Modules\TipoProcedimientoTupa\Infrastructure\Providers\TipoProcedimientoTupaServiceProvider::class,
    App\Modules\Personas\Infrastructure\Providers\PersonasServiceProvider::class,
    App\Modules\Giros\Infrastructure\Providers\GirosServiceProvider::class,
    App\Modules\TipoDocumentoIdentidad\Infrastructure\Providers\TipoDocumentoIdentidadServiceProvider::class,
    App\Modules\Zonificacion\Infrastructure\Providers\ZonificacionServiceProvider::class,
    App\Modules\NivelRiesgo\Infrastructure\Providers\NivelRiesgoServiceProvider::class,
    AppServiceProvider::class,
    EstadosServiceProvider::class,
    App\Modules\Licencias\Infrastructure\Providers\LicenciasServiceProvider::class,
    App\Modules\ITSE\Infrastructure\Providers\ItseServiceProvider::class,
    App\Modules\TipoLicencia\Infrastructure\Providers\TipoLicenciaServiceProvider::class,
    App\Modules\Expedientes\Infrastructure\Providers\ExpedienteServiceProvider::class,
];
