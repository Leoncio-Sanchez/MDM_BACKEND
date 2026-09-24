<?php

declare(strict_types=1);

namespace App\Modules\Seguridad\Application\UseCases;

use App\Modules\Seguridad\Application\DTOs\VerifyTokenDTO;
use App\Modules\Seguridad\Domain\Contracts\TokenServiceInterface;

final readonly class VerificarTokenUseCase
{
    public function __construct(private TokenServiceInterface $tokens) {}

    public function execute(VerifyTokenDTO $dto): bool
    {
        return $this->tokens->verify($dto->token);
    }
}
