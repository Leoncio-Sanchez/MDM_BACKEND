<?php

declare(strict_types=1);

namespace App\Modules\Usuarios\Domain;

/** Largo mínimo de la contraseña de una cuenta de personal o de inspector. */
final class ReglaContrasena
{
    public const MINIMO = 6;

    public const MENSAJE = 'La contraseña debe tener al menos '.self::MINIMO.' caracteres.';

    public static function cumple(mixed $password): bool
    {
        return is_string($password) && mb_strlen(trim($password)) >= self::MINIMO;
    }
}
