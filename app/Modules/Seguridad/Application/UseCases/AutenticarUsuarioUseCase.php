<?php

declare(strict_types=1);

namespace App\Modules\Seguridad\Application\UseCases;

use App\Modules\Seguridad\Application\DTOs\LoginDTO;
use App\Modules\Seguridad\Domain\Contracts\PasswordHasherInterface;
use App\Modules\Seguridad\Domain\Contracts\TokenServiceInterface;
use App\Modules\Seguridad\Domain\Contracts\UserRepositoryInterface;
use App\Modules\Seguridad\Domain\Exceptions\CredencialesInvalidas;
use App\Modules\Seguridad\Domain\ValueObjects\TokenPair;

final readonly class AutenticarUsuarioUseCase
{
    public function __construct(
        private UserRepositoryInterface $users,
        private PasswordHasherInterface $hasher,
        private TokenServiceInterface $tokens,
    ) {}

    public function execute(LoginDTO $dto): TokenPair
    {
        $user = $this->users->buscarPorUsername($dto->username);

        if ($user === null) {
            throw CredencialesInvalidas::default();
        }

        if (! $this->hasher->verify($dto->password, $user->passwordHash)) {
            throw CredencialesInvalidas::default();
        }

        return $this->tokens->issue($user);
    }
}
