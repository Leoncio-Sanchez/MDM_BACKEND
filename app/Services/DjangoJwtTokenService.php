<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\Seguridad\TokenInvalido;
use App\Models\User;
use DateTimeImmutable;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Symfony\Component\Clock\NativeClock;
use Throwable;

final readonly class DjangoJwtTokenService
{
    private string $secret;

    private int $accessTtlSeconds;

    private int $refreshTtlSeconds;

    public function __construct(
        ?string $secret = null,
        ?int $accessTtlSeconds = null,
        ?int $refreshTtlSeconds = null,
    ) {
        $secretVal = $secret ?? (string) config('jwt.secret', 'django-insecure-dev-key-change-in-production');
        $this->secret = $secretVal !== '' ? $secretVal : 'django-insecure-dev-key-change-in-production';
        $accessHours = (int) config('jwt.access_lifetime_hours', 1);
        $refreshDays = (int) config('jwt.refresh_lifetime_days', 7);
        $this->accessTtlSeconds = $accessTtlSeconds ?? max(1, $accessHours * 3600);
        $this->refreshTtlSeconds = $refreshTtlSeconds ?? max(1, $refreshDays * 86400);
    }

    public function issue(AuthenticatedUser|User $user): TokenPair
    {
        $authUser = $user instanceof User ? AuthenticatedUser::fromModel($user) : $user;

        return new TokenPair(
            access: $this->buildToken($authUser, 'access', $this->accessTtlSeconds),
            refresh: $this->buildToken($authUser, 'refresh', $this->refreshTtlSeconds),
        );
    }

    public function refresh(string $refreshToken): TokenPair
    {
        $user = $this->decode($refreshToken);

        if ($this->getTokenType($refreshToken) !== 'refresh') {
            throw TokenInvalido::default();
        }

        // ROTATE_REFRESH_TOKENS=True de Django -> devuelve AMBOS tokens nuevos
        return new TokenPair(
            access: $this->buildToken($user, 'access', $this->accessTtlSeconds),
            refresh: $this->buildToken($user, 'refresh', $this->refreshTtlSeconds),
        );
    }

    public function verify(string $token): bool
    {
        try {
            $this->decode($token);

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    public function decode(string $token): AuthenticatedUser
    {
        if ($token === '') {
            throw TokenInvalido::default();
        }

        $config = $this->getConfiguration();

        try {
            $parsed = $config->parser()->parse($token);

            if (! $parsed instanceof Plain) {
                throw TokenInvalido::default();
            }

            $clock = new NativeClock;
            $config->validator()->assert(
                $parsed,
                new SignedWith($config->signer(), $config->verificationKey()),
                new LooseValidAt($clock),
            );

            $claims = $parsed->claims();
            $userId = $claims->get('user_id') ?? $claims->get('sub');

            if ($userId === null || ! is_numeric($userId)) {
                throw TokenInvalido::default();
            }

            $rawRuc = $claims->get('ruc');
            $ruc = is_string($rawRuc) && $rawRuc !== '' ? $rawRuc : null;

            return new AuthenticatedUser(
                id: (int) $userId,
                username: (string) $claims->get('username', ''),
                email: (string) $claims->get('email', ''),
                role: (string) $claims->get('role', 'CIUDADANO'),
                ruc: $ruc,
                passwordHash: '',
            );
        } catch (Throwable) {
            throw TokenInvalido::default();
        }
    }

    private function buildToken(AuthenticatedUser $user, string $type, int $ttl): string
    {
        $now = (new DateTimeImmutable)->setTimestamp(time());
        $config = $this->getConfiguration();

        return $config->builder()
            ->issuedAt($now)
            ->expiresAt($now->modify("+{$ttl} seconds"))
            ->identifiedBy(bin2hex(random_bytes(16)))
            ->withClaim('token_type', $type)
            ->withClaim('user_id', (string) $user->id)
            ->withClaim('username', $user->username)
            ->withClaim('role', $user->role)
            ->withClaim('ruc', $user->ruc)
            ->getToken($config->signer(), $config->signingKey())
            ->toString();
    }

    private function getTokenType(string $token): string
    {
        if ($token === '') {
            return '';
        }

        try {
            $config = $this->getConfiguration();
            $parsed = $config->parser()->parse($token);

            if (! $parsed instanceof Plain) {
                return '';
            }

            return (string) $parsed->claims()->get('token_type', '');
        } catch (Throwable) {
            return '';
        }
    }

    private function getConfiguration(): Configuration
    {
        return Configuration::forSymmetricSigner(
            new Sha256,
            InMemory::plainText($this->secret),
        );
    }
}
