<?php
declare(strict_types=1);

namespace App\Modules\Expedientes\Domain\Exceptions;

use DomainException;

final class ExpedienteInvalido extends DomainException
{
    public static function numeroInvalido(int $numero): self
    {
        return new self("El número de expediente {$numero} no es válido. Debe ser mayor a 0.");
    }

    public static function numeroDuplicado(int $numero, int $anio): self
    {
        return new self("Ya existe un expediente con el número {$numero} en el año {$anio}.");
    }

    public static function conLicenciaEmitida(int $id): self
    {
        return new self("No se puede eliminar el expediente {$id} porque tiene una Licencia de Funcionamiento asociada.");
    }

    public static function conItseEmitido(int $id): self
    {
        return new self("No se puede eliminar el expediente {$id} porque tiene un ITSE asociado.");
    }
}
