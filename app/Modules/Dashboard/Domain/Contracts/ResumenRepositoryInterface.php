<?php

declare(strict_types=1);

namespace App\Modules\Dashboard\Domain\Contracts;

use App\Modules\Dashboard\Domain\ValueObjects\ResumenGeneral;
use DateTimeImmutable;

interface ResumenRepositoryInterface
{
    /** Cifras a la fecha "hoy"; "por vencer" en ITSE = caducan dentro de $diasAlertaItse días. */
    public function resumen(DateTimeImmutable $hoy, int $diasAlertaItse): ResumenGeneral;
}
