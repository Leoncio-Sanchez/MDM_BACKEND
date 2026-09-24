<?php

declare(strict_types=1);

namespace App\Modules\Seguridad\Application\UseCases;

use App\Modules\Seguridad\Application\DTOs\ObtenerUsuarioDTO;
use App\Modules\Seguridad\Domain\Contracts\UserRepositoryInterface;
use App\Modules\Seguridad\Domain\Exceptions\UsuarioNoEncontrado;
use App\Modules\Seguridad\Domain\ValueObjects\AuthenticatedUser;

final readonly class ObtenerUsuarioAutenticadoUseCase
{
    public function __construct(private UserRepositoryInterface $users) {}

    public function execute(ObtenerUsuarioDTO $dto): AuthenticatedUser
    {
        return $this->users->buscarPorId($dto->userId)
            ?? throw UsuarioNoEncontrado::porId($dto->userId);
    }
}
