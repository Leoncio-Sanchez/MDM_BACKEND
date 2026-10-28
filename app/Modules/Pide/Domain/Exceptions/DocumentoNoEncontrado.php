<?php

declare(strict_types=1);

namespace App\Modules\Pide\Domain\Exceptions;

use DomainException;

/** El servicio respondió bien, pero el DNI o RUC no figura en su registro. */
final class DocumentoNoEncontrado extends DomainException
{
    public static function dni(): self
    {
        return new self('No se encontró ese DNI en RENIEC. Verifique el número o registre los datos manualmente.');
    }

    public static function ruc(): self
    {
        return new self('No se encontró ese RUC en SUNAT. Verifique el número o registre los datos manualmente.');
    }
}
