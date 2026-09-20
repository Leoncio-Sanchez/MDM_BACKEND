<?php

declare(strict_types=1);

namespace App\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\StrictValidAt;
use Lcobucci\Clock\SystemClock;
use App\Models\User;

class DjangoJwtUserProvider implements UserProvider
{
    public function retrieveById($identifier): ?Authenticatable
    {
        return User::find($identifier);
    }

    public function retrieveByToken($identifier, $token): ?Authenticatable
    {
        return null;
    }

    public function updateRememberToken(Authenticatable $user, $token): void {}

    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        if (isset($credentials['jwt_token'])) {
            return $this->parseToken($credentials['jwt_token']);
        }
        return User::where('username', $credentials['username'] ?? '')->first();
    }

    private function parseToken(string $token): ?Authenticatable
    {
        try {
            $secret = config('jwt.secret');
            $config = Configuration::forSymmetricSigner(
                new Sha256(),
                InMemory::plainText($secret)
            );
            $parsed = $config->parser()->parse($token);
            
            // Validate signature
            $config->validator()->assert($parsed, new SignedWith($config->signer(), $config->signingKey()));
            
            // Get user_id claim (Django simplejwt style)
            $userId = $parsed->claims()->get('user_id') ?? $parsed->claims()->get('sub');
            if (!$userId) return null;
            
            return User::find((int)$userId);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        return true;
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false): void {}
}
