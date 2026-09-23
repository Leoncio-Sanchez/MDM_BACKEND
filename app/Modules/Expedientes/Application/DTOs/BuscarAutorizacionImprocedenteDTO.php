<?php

declare(strict_types=1);

namespace App\Modules\Expedientes\Application\DTOs;

use App\Modules\Expedientes\Domain\Exceptions\TipoAutorizacionInvalidoError;
use App\Modules\Expedientes\Domain\ValueObjects\TipoAutorizacion;

final readonly class BuscarAutorizacionImprocedenteDTO
{
    public function __construct(
        public int $expedienteId,
        public TipoAutorizacion $tipo,
    ) {}

    public static function fromParams(int $expedienteId, string $tipoStr): self
    {
        $tipoUpper = strtoupper(trim($tipoStr));
        $tipo = TipoAutorizacion::tryFrom($tipoUpper);
        if ($tipo === null) {
            throw TipoAutorizacionInvalidoError::paraTipo($tipoStr);
        }

        return new self($expedienteId, $tipo);
    }
}
