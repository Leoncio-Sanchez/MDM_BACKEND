<?php

declare(strict_types=1);

namespace App\Modules\Seguridad\Presentation\Resources;

use App\Modules\Seguridad\Domain\ValueObjects\AuthenticatedUser;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin AuthenticatedUser */
final class UserResource extends JsonResource
{
    /** @return array{email: string, id: int, username: string, role: string, permisos: array{expedientes: bool, licencias: bool, itse: bool, admin: bool, inspector: bool, jefe_itse: bool, reportes: bool}} */
    public function toArray(Request $request): array
    {
        return [
            'email' => $this->email,
            'id' => $this->id,
            'username' => $this->username,
            'role' => $this->role,
            'permisos' => $this->permisos->toArray(),
        ];
    }
}
