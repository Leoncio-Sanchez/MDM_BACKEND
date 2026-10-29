<?php

declare(strict_types=1);

namespace App\Modules\Expedientes\Domain\Exceptions;

use App\Modules\Expedientes\Domain\ValueObjects\TipoAutorizacion;
use DomainException;

final class DenegatoriaInvalida extends DomainException
{
    public static function yaRegistrada(int $numeroExpediente, TipoAutorizacion $tipo): self
    {
        return new self("El expediente N° {$numeroExpediente} ya tiene registrada la denegatoria de ".self::que($tipo).'.');
    }

    public static function noRequiere(int $numeroExpediente, TipoAutorizacion $tipo): self
    {
        return new self("El procedimiento del expediente N° {$numeroExpediente} no incluye ".self::que($tipo).': no hay nada que denegar.');
    }

    private static function que(TipoAutorizacion $tipo): string
    {
        return $tipo === TipoAutorizacion::ITSE ? 'la ITSE' : 'la licencia de funcionamiento';
    }
}
