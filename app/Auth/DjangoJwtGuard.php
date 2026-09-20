<?php

declare(strict_types=1);

namespace App\Auth;

use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use App\Models\User;

class DjangoJwtGuard implements Guard
{
    use GuardHelpers;

    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function user(): ?Authenticatable
    {
        if ($this->user !== null) {
            return $this->user;
        }

        $token = $this->getTokenFromRequest();
        if (!$token) return null;

        try {
            $secret = config('jwt.secret');
            $config = Configuration::forSymmetricSigner(
                new Sha256(),
                InMemory::plainText($secret)
            );
            
            $parsed = $config->parser()->parse($token);
            
            // Validate signature only (not exp for now)
            $config->validator()->assert(
                $parsed,
                new \Lcobucci\JWT\Validation\Constraint\SignedWith(
                    $config->signer(),
                    $config->signingKey()
                )
            );
            
            // Django simplejwt uses user_id, standard JWT uses sub
            $userId = $parsed->claims()->get('user_id') ?? $parsed->claims()->get('sub');
            
            if (!$userId) return null;
            
            $this->user = User::find((int)$userId);
            return $this->user;
            
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function validate(array $credentials = []): bool
    {
        return $this->user !== null;
    }

    public function setUser(Authenticatable $user): static
    {
        $this->user = $user;
        return $this;
    }

    protected function getTokenFromRequest(): ?string
    {
        $header = $this->request->header('Authorization', '');
        if (str_starts_with($header, 'Bearer ')) {
            return substr($header, 7);
        }
        return null;
    }
}
