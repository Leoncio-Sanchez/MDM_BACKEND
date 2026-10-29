<?php

declare(strict_types=1);

namespace App\Modules\Inspecciones\Domain\Exceptions;

final class AsignacionInvalida extends \DomainException
{
    public static function inspectorNoValido(): self
    {
        return new self('El inspector no existe, está desactivado o no está marcado como inspector.');
    }

    public static function expedienteSinInspeccionPendiente(int $numero): self
    {
        return new self("El expediente N° {$numero} no espera inspección ITSE: no la requiere, ya tiene dictamen final, certificado o denegatoria, o está anulado.");
    }

    public static function mismoInspector(): self
    {
        return new self('El expediente ya está asignado a ese inspector. Elija otro inspector o cambie solo la programación.');
    }

    public static function acompananteNoValido(): self
    {
        return new self('Uno de los acompañantes no existe, está desactivado o no está marcado como inspector.');
    }

    public static function responsableComoAcompanante(): self
    {
        return new self('El inspector responsable no puede figurar también como acompañante.');
    }

    public static function demasiadosAcompanantes(int $maximo): self
    {
        return new self("Se permiten como máximo {$maximo} acompañantes por inspección.");
    }
}
