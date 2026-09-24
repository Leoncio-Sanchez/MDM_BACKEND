<?php

declare(strict_types=1);

namespace App\Modules\Seguridad\Infrastructure\Hashing;

use App\Modules\Seguridad\Domain\Contracts\PasswordHasherInterface;

final readonly class DjangoPbkdf2Hasher implements PasswordHasherInterface
{
    public function verify(string $plainPassword, string $djangoHash): bool
    {
        // Formato: pbkdf2_sha256$<iterations>$<salt>$<base64_hash>
        $parts = explode('$', $djangoHash);

        if (count($parts) !== 4) {
            return false;
        }

        [$algorithm, $iterations, $salt, $expectedHash] = $parts;

        if ($algorithm !== 'pbkdf2_sha256') {
            return false;
        }

        $iterationsInt = (int) $iterations;
        if ($iterationsInt <= 0) {
            return false;
        }

        $computedBinary = hash_pbkdf2(
            'sha256',
            $plainPassword,
            $salt,
            $iterationsInt,
            0,
            true,
        );

        $computedB64 = base64_encode($computedBinary);

        return hash_equals($expectedHash, $computedB64);
    }
}
