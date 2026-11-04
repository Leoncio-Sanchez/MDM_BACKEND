<?php

declare(strict_types=1);

namespace App\Modules\Usuarios\Domain;

/**
 * Largo mínimo al crear o cambiar la contraseña de una cuenta de personal o de inspector.
 * El login no la aplica: las cuentas con una contraseña más corta siguen entrando.
 */
final class ReglaContrasena
{
    public const MINIMO = 10;

    public const MENSAJE = 'La contraseña debe tener al menos '.self::MINIMO.' caracteres.';

    public static function cumple(mixed $password): bool
    {
        return is_string($password) && mb_strlen(trim($password)) >= self::MINIMO;
    }
}
