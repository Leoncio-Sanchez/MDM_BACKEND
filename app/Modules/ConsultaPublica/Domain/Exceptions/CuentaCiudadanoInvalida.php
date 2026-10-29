<?php

declare(strict_types=1);

namespace App\Modules\ConsultaPublica\Domain\Exceptions;

final class CuentaCiudadanoInvalida extends \DomainException
{
    /** El mismo mensaje si el documento no existe o si el expediente no es suyo: no revela qué documentos hay */
    public static function datosNoCoinciden(): self
    {
        return new self('El número de expediente no corresponde a ese DNI o RUC. Revise su cargo de recepción: el expediente debe estar a su nombre (como solicitante o representante).');
    }

    public static function yaExiste(): self
    {
        return new self('Ese DNI o RUC ya tiene una cuenta. Ingrese con su contraseña o, si la olvidó, use «Olvidé mi contraseña».');
    }

    public static function noExiste(): self
    {
        return new self('Ese DNI o RUC todavía no tiene una cuenta. Créela con «Crear cuenta».');
    }

    public static function claveIgualAlDocumento(): self
    {
        return new self('La contraseña no puede ser su DNI o RUC.');
    }
}
