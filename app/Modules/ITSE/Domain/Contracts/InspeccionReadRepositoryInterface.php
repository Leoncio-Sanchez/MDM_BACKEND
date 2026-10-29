<?php

declare(strict_types=1);

namespace App\Modules\ITSE\Domain\Contracts;

use App\Modules\ITSE\Domain\ValueObjects\SituacionInspeccion;

/** Resultado de la inspección (módulo Inspecciones) que sustenta el certificado ITSE */
interface InspeccionReadRepositoryInterface
{
    /** null si el expediente no existe */
    public function situacion(int $expedienteId): ?SituacionInspeccion;
}
