<?php

declare(strict_types=1);

namespace App\Modules\Seguridad\Application\UseCases;

use App\Modules\Seguridad\Application\DTOs\RefreshTokenDTO;
use App\Modules\Seguridad\Domain\Contracts\TokenServiceInterface;
use App\Modules\Seguridad\Domain\ValueObjects\TokenPair;

final readonly class RefrescarTokenUseCase
{
    public function __construct(private TokenServiceInterface $tokens) {}

    public function execute(RefreshTokenDTO $dto): TokenPair
    {
        // Django con ROTATE_REFRESH_TOKENS=True devuelve AMBOS tokens nuevos.
        return $this->tokens->refresh($dto->refresh);
    }
}
