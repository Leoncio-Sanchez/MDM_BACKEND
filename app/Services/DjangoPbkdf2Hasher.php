<?php

declare(strict_types=1);

namespace App\Services;

class DjangoPbkdf2Hasher
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

    public function hash(string $plainPassword): string
    {
        return $this->makeHash($plainPassword);
    }

    public function makeHash(string $plainPassword, int $iterations = 720000, ?string $salt = null): string
    {
        if ($iterations < 1) {
            $iterations = 720000;
        }

        $salt ??= bin2hex(random_bytes(11));

        $binary = hash_pbkdf2(
            'sha256',
            $plainPassword,
            $salt,
            $iterations,
            0,
            true
        );

        $b64 = base64_encode($binary);

        return sprintf('pbkdf2_sha256$%d$%s$%s', $iterations, $salt, $b64);
    }
}
