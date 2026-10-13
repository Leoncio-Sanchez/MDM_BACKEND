<?php

declare(strict_types=1);

namespace App\Modules\Seguridad\Domain\ValueObjects;

/**
 * Roles del sistema (claim `role` del JWT).
 *
 * Regla (Django, FASE_9): staff/superuser → ADMIN_MUNICIPALIDAD; resto → CIUDADANO.
 * Extensión Laravel: un usuario no administrador cuyo perfil (`usuarios_perfiles.es_inspector`)
 * lo marca como inspector recibe INSPECTOR, para acceder a su portal.
 */
enum Rol: string
{
    case AdminMunicipalidad = 'ADMIN_MUNICIPALIDAD';
    case Inspector = 'INSPECTOR';
    case Ciudadano = 'CIUDADANO';

    public static function resolver(bool $esAdmin, bool $esInspector): self
    {
        if ($esAdmin) {
            return self::AdminMunicipalidad;
        }

        return $esInspector ? self::Inspector : self::Ciudadano;
    }
}
