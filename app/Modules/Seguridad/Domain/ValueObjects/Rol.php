<?php

declare(strict_types=1);

namespace App\Modules\Seguridad\Domain\ValueObjects;

/**
 * Roles del sistema (claim `role` del JWT).
 *
 * Regla (Django, FASE_9): staff/superuser → ADMIN_MUNICIPALIDAD; resto → CIUDADANO.
 *
 * No existe un rol INSPECTOR: en el esquema real los inspectores son registros de la
 * tabla `inspectores` (no usuarios con login) y `usuarios_perfiles` no tiene ninguna
 * columna que marque a un usuario como inspector.
 */
enum Rol: string
{
    case AdminMunicipalidad = 'ADMIN_MUNICIPALIDAD';
    case Ciudadano = 'CIUDADANO';

    public static function resolver(bool $esAdmin): self
    {
        return $esAdmin ? self::AdminMunicipalidad : self::Ciudadano;
    }
}
