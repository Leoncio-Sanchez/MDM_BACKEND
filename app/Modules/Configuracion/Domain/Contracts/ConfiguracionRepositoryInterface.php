<?php

declare(strict_types=1);

namespace App\Modules\Configuracion\Domain\Contracts;

use App\Modules\Configuracion\Domain\ValueObjects\ConfiguracionInforme;

interface ConfiguracionRepositoryInterface
{
    public function informe(): ConfiguracionInforme;

    public function guardarInforme(ConfiguracionInforme $configuracion, int $usuarioId): void;
}
