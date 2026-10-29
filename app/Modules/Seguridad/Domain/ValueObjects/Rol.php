<?php

declare(strict_types=1);

namespace App\Modules\Seguridad\Domain\ValueObjects;

/**
 * Roles del sistema (claim `role` del JWT).
 *
 * Regla (Django, FASE_9): staff/superuser → ADMIN_MUNICIPALIDAD; resto → CIUDADANO.
 *
 * INSPECTOR: usuario que no es staff y tiene la casilla es_inspector. Es un rol aparte
 * para que el inspector de campo solo llegue a su bandeja y no a las rutas generales
 * del personal (Personas, Archivos, consultas PIDE).
 */
enum Rol: string
{
    case AdminMunicipalidad = 'ADMIN_MUNICIPALIDAD';
    case Ciudadano = 'CIUDADANO';
    case Inspector = 'INSPECTOR';

    public static function resolver(bool $esAdmin, bool $esInspector = false): self
    {
        return match (true) {
            $esAdmin => self::AdminMunicipalidad,
            $esInspector => self::Inspector,
            default => self::Ciudadano,
        };
    }
}
