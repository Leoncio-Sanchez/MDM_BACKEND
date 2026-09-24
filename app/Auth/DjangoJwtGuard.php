<?php

declare(strict_types=1);

namespace App\Auth;

use App\Models\User;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Symfony\Component\Clock\NativeClock;
use Throwable;

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
        if ($token === null) {
            return null;
        }

        try {
            $secret = (string) config('jwt.secret');
            if ($secret === '') {
                return null;
            }

            $config = Configuration::forSymmetricSigner(
                new Sha256,
                InMemory::plainText($secret)
            );

            $parsed = $config->parser()->parse($token);
            if (! $parsed instanceof Plain) {
                return null;
            }

            $clock = new NativeClock;
            $config->validator()->assert(
                $parsed,
                new SignedWith($config->signer(), $config->verificationKey()),
                new LooseValidAt($clock),
            );

            // Django SimpleJWT usa 'user_id', JWT estándar usa 'sub'
            $userId = $parsed->claims()->get('user_id') ?? $parsed->claims()->get('sub');
            if ($userId === null || ! is_numeric($userId)) {
                return null;
            }

            $user = User::find((int) $userId);
            if ($user === null || (isset($user->is_active) && ! $user->is_active)) {
                return null;
            }

            $this->user = $user;
            $this->request->setUserResolver(fn (): ?Authenticatable => $this->user);

            return $this->user;
        } catch (Throwable) {
            return null;
        }
    }

    public function validate(array $credentials = []): bool
    {
        return $this->user() !== null;
    }

    public function setUser(Authenticatable $user): static
    {
        $this->user = $user;
        $this->request->setUserResolver(fn (): ?Authenticatable => $this->user);

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
