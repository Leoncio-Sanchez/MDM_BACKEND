<?php

declare(strict_types=1);

namespace Tests\Fixtures;

/**
 * Datos válidos para insertar en `auth_user` desde los tests.
 *
 * El esquema real (Django) no tiene valores por defecto: first_name, last_name, email,
 * is_superuser, is_staff, is_active y date_joined son NOT NULL. Este helper completa los
 * campos obligatorios y deja que cada test indique solo lo que le importa.
 *
 *   DB::table('auth_user')->insert(UsuarioAuth::datos(['id' => 1, 'username' => 'admin']));
 */
final class UsuarioAuth
{
    /**
     * @param  array<string, mixed>  $datos
     * @return array<string, mixed>
     */
    public static function datos(array $datos = []): array
    {
        return array_merge([
            'password' => 'secret',
            'first_name' => '',
            'last_name' => '',
            'email' => '',
            'is_superuser' => false,
            'is_staff' => false,
            'is_active' => true,
            'date_joined' => now(),
        ], $datos);
    }
}
