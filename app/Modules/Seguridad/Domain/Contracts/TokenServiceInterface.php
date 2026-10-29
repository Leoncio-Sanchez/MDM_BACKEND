<?php

declare(strict_types=1);

namespace App\Modules\Seguridad\Domain\Contracts;

use App\Modules\Seguridad\Domain\ValueObjects\AuthenticatedUser;
use App\Modules\Seguridad\Domain\ValueObjects\TokenPair;

interface TokenServiceInterface
{
    /**
     * Genera un par access + refresh con los claims exactos de Django.
     *
     * Claims del payload:
     * - token_type: "access" | "refresh"
     * - exp, iat: timestamps Unix
     * - jti: string random hex
     * - user_id: STRING (no int)
     * - username: string
     * - role: "ADMIN_MUNICIPALIDAD" | "INSPECTOR" | "CIUDADANO"
     * - ruc: string|null
     */
    public function issue(AuthenticatedUser $user): TokenPair;

    /**
     * Refresca un access token a partir de un refresh token válido.
     * Replica ROTATE_REFRESH_TOKENS: True de Django → devuelve AMBOS tokens.
     */
    public function refresh(string $refreshToken): TokenPair;

    /**
     * Verifica un token sin renovar.
     */
    public function verify(string $token): bool;

    /**
     * Decodifica y valida un token, devolviendo el AuthenticatedUser.
     */
    public function decode(string $token): AuthenticatedUser;
}
