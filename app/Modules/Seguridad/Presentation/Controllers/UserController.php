<?php

declare(strict_types=1);

namespace App\Modules\Seguridad\Presentation\Controllers;

use App\Modules\Seguridad\Application\DTOs\ObtenerUsuarioDTO;
use App\Modules\Seguridad\Application\UseCases\ObtenerUsuarioAutenticadoUseCase;
use App\Modules\Seguridad\Presentation\Resources\UserResource;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;

final readonly class UserController
{
    public function __construct(
        private ObtenerUsuarioAutenticadoUseCase $obtener,
    ) {}

    public function me(Request $request): UserResource
    {
        /** @var Authenticatable $user */
        $user = $request->user();
        $userId = (int) $user->getAuthIdentifier();

        $authenticatedUser = $this->obtener->execute(
            new ObtenerUsuarioDTO(userId: $userId),
        );

        return new UserResource($authenticatedUser);
    }
}
