<?php

declare(strict_types=1);

namespace App\Controllers\Auth;

use App\Exceptions\Seguridad\CredencialesInvalidas;
use App\Exceptions\Seguridad\TokenInvalido;
use App\Models\User;
use App\Requests\Auth\LoginRequest;
use App\Requests\Auth\RefreshTokenRequest;
use App\Requests\Auth\VerifyTokenRequest;
use App\Services\DjangoJwtTokenService;
use App\Services\DjangoPbkdf2Hasher;
use Illuminate\Http\JsonResponse;
use stdClass;

final class JwtAuthController
{
    public function __construct(
        private readonly DjangoJwtTokenService $tokens,
        private readonly DjangoPbkdf2Hasher $hasher,
    ) {}

    public function create(LoginRequest $request): JsonResponse
    {
        $username = (string) $request->input('username');
        $password = (string) $request->input('password');

        $user = User::where('username', $username)
            ->where('is_active', true)
            ->first();

        if ($user === null || ! $this->hasher->verify($password, (string) $user->password)) {
            return response()->json(
                ['detail' => CredencialesInvalidas::default()->getMessage()],
                401,
            );
        }

        $tokenPair = $this->tokens->issue($user);

        return response()->json($tokenPair->toArray(), 200);
    }

    public function refresh(RefreshTokenRequest $request): JsonResponse
    {
        $refresh = (string) $request->input('refresh');

        try {
            $tokenPair = $this->tokens->refresh($refresh);
        } catch (TokenInvalido $e) {
            return response()->json(
                ['detail' => $e->getMessage()],
                401,
            );
        }

        return response()->json($tokenPair->toArray(), 200);
    }

    public function verify(VerifyTokenRequest $request): JsonResponse
    {
        $token = (string) $request->input('token');

        if (! $this->tokens->verify($token)) {
            return response()->json(['detail' => 'Token is invalid or expired'], 401);
        }

        return response()->json(new stdClass, 200);
    }
}
