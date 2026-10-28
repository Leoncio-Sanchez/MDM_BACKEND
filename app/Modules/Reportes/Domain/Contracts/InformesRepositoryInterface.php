<?php

declare(strict_types=1);

namespace App\Modules\Reportes\Domain\Contracts;

use App\Modules\Reportes\Domain\ValueObjects\InformeEmitido;

interface InformesRepositoryInterface
{
    /**
     * Asigna el siguiente correlativo del año al periodo. Si el periodo ya tenía informe,
     * devuelve el mismo número (reimpresión): no se consumen ni se repiten números.
     */
    public function emitir(string $tipo, string $desde, string $hasta, int $usuarioId): InformeEmitido;
}
