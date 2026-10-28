<?php

declare(strict_types=1);

namespace App\Modules\Seguridad\Domain\ValueObjects;

/**
 * Personal municipal activo (staff, no superusuario) que al iniciar sesión no vería
 * ningún módulo: no tiene fila en usuarios_perfiles, o la tiene con todo desmarcado.
 */
final readonly class PersonalSinAccesos
{
    public const SIN_PERFIL = 'Sin perfil de accesos';
    public const SIN_ACCESOS = 'Perfil con todos los accesos desmarcados';

    public function __construct(
        public int $id,
        public string $username,
        public string $nombre,
        public string $motivo,
    ) {}
}
