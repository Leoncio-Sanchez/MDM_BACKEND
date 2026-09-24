<?php

declare(strict_types=1);

namespace App\Modules\Seguridad\Domain\Contracts;

interface PasswordHasherInterface
{
    /**
     * Verifica una contraseña en texto plano contra un hash de Django.
     *
     * Formato del hash: pbkdf2_sha256$<iterations>$<salt>$<base64_hash>
     *
     * @param  string  $plainPassword  Contraseña en texto plano.
     * @param  string  $djangoHash  Hash almacenado en auth_user.password.
     * @return bool true si coincide.
     */
    public function verify(string $plainPassword, string $djangoHash): bool;

    /**
     * Genera un hash compatible con Django en formato pbkdf2_sha256.
     */
    public function hash(string $plainPassword): string;

    public function makeHash(string $plainPassword, int $iterations = 720000, ?string $salt = null): string;
}
