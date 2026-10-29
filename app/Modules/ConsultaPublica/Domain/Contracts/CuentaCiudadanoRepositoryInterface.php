<?php

declare(strict_types=1);

namespace App\Modules\ConsultaPublica\Domain\Contracts;

/** Cuentas de ciudadanos (tabla ciudadanos_cuentas + auth_user "ciu_<documento>"). */
interface CuentaCiudadanoRepositoryInterface
{
    /** Usuario interno de la cuenta: no choca con el personal que use su DNI como usuario */
    public const PREFIJO_USUARIO = 'ciu_';

    /** ¿El documento es del solicitante o del representante de algún expediente con ese número? */
    public function esParteDeExpediente(string $documento, int $numeroExpediente): bool;

    public function existeCuenta(string $documento): bool;

    /** Crea el usuario (no es personal ni tiene perfil de accesos) y lo vincula al documento. */
    public function crear(string $documento, string $hashClave): void;

    public function cambiarClave(string $documento, string $hashClave): void;

    public function documentoDelUsuario(int $usuarioId): ?string;
}
