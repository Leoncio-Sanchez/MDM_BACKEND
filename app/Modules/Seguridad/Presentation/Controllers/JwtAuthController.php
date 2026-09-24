<?php

declare(strict_types=1);

namespace App\Modules\Seguridad\Presentation\Controllers;

use App\Modules\Seguridad\Application\DTOs\LoginDTO;
use App\Modules\Seguridad\Application\DTOs\RefreshTokenDTO;
use App\Modules\Seguridad\Application\DTOs\VerifyTokenDTO;
use App\Modules\Seguridad\Application\UseCases\AutenticarUsuarioUseCase;
use App\Modules\Seguridad\Application\UseCases\RefrescarTokenUseCase;
use App\Modules\Seguridad\Application\UseCases\VerificarTokenUseCase;
use App\Modules\Seguridad\Domain\Exceptions\CredencialesInvalidas;
use App\Modules\Seguridad\Domain\Exceptions\TokenInvalido;
use App\Modules\Seguridad\Presentation\Requests\LoginRequest;
use App\Modules\Seguridad\Presentation\Requests\RefreshTokenRequest;
use App\Modules\Seguridad\Presentation\Requests\VerifyTokenRequest;
use Illuminate\Http\JsonResponse;
use stdClass;

final readonly class JwtAuthController
{
    public function __construct(
        private AutenticarUsuarioUseCase $autenticar,
        private RefrescarTokenUseCase $refrescar,
        private VerificarTokenUseCase $verificar,
    ) {}

    public function create(LoginRequest $request): JsonResponse
    {
        try {
            $tokens = $this->autenticar->execute(
                LoginDTO::fromArray($request->validated()),
            );
        } catch (CredencialesInvalidas $e) {
            return response()->json(
                ['detail' => $e->getMessage()],
                401,
            );
        }

        return response()->json($tokens->toArray(), 200);
    }

    public function refresh(RefreshTokenRequest $request): JsonResponse
    {
        try {
            $tokens = $this->refrescar->execute(
                RefreshTokenDTO::fromArray($request->validated()),
            );
        } catch (TokenInvalido $e) {
            return response()->json(
                ['detail' => $e->getMessage()],
                401,
            );
        }

        return response()->json($tokens->toArray(), 200);
    }

    public function verify(VerifyTokenRequest $request): JsonResponse
    {
        $valid = $this->verificar->execute(
            VerifyTokenDTO::fromArray($request->validated()),
        );

        if (! $valid) {
            return response()->json(['detail' => 'Token is invalid or expired'], 401);
        }

        return response()->json(new stdClass, 200);
    }
}
