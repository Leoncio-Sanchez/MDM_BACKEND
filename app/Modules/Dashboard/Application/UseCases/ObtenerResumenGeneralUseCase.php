<?php

declare(strict_types=1);

namespace App\Modules\Dashboard\Application\UseCases;

use App\Modules\Dashboard\Domain\Contracts\ResumenRepositoryInterface;
use App\Modules\Dashboard\Domain\ValueObjects\ResumenGeneral;
use Carbon\CarbonImmutable;

final readonly class ObtenerResumenGeneralUseCase
{
    /** Igual que el listado "ITSE por renovar": avisar con 30 días de anticipación. */
    public const DIAS_ALERTA_ITSE = 30;

    public function __construct(private ResumenRepositoryInterface $resumenes) {}

    public function execute(): ResumenGeneral
    {
        return $this->resumenes->resumen(CarbonImmutable::today(), self::DIAS_ALERTA_ITSE);
    }
}
