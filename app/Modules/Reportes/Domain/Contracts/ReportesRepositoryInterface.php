<?php

declare(strict_types=1);

namespace App\Modules\Reportes\Domain\Contracts;

use App\Modules\Reportes\Domain\ValueObjects\BajaRegistrada;
use App\Modules\Reportes\Domain\ValueObjects\Improcedencia;
use App\Modules\Reportes\Domain\ValueObjects\LicenciaPadron;
use App\Modules\Reportes\Domain\ValueObjects\TipoAutorizacion;

interface ReportesRepositoryInterface
{
    /**
     * Licencias emitidas entre las fechas (Y-m-d, inclusive), por número de licencia.
     *
     * @return list<LicenciaPadron>
     */
    public function padronLicencias(string $desde, string $hasta): array;

    /** @return list<string> nombres de los niveles de riesgo, en orden */
    public function nivelesRiesgo(): array;

    /**
     * Bajas (cambios a un estado inactivo) con fecha de estado entre las fechas, de la más antigua a la más reciente.
     *
     * @return list<BajaRegistrada>
     */
    public function bajas(TipoAutorizacion $tipo, string $desde, string $hasta): array;

    /** @return list<string> estados inactivos que aplican al tipo (Anulada, Revocada…), por nombre */
    public function estadosDeBaja(TipoAutorizacion $tipo): array;

    /**
     * Improcedencias con fecha de rechazo entre las fechas, de la más antigua a la más reciente.
     *
     * @return list<Improcedencia>
     */
    public function improcedencias(TipoAutorizacion $tipo, string $desde, string $hasta): array;
}
