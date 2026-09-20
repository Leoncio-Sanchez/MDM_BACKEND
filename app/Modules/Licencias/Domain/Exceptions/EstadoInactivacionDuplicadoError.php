<?php
declare(strict_types=1);
namespace App\Modules\Licencias\Domain\Exceptions;

final class EstadoInactivacionDuplicadoError extends \DomainException
{
    public static function porEstado(int $licenciaId, int $estadoId): self
    {
        return new self("Ya existe un registro para la licencia {$licenciaId} con el estado {$estadoId}.");
    }
}
