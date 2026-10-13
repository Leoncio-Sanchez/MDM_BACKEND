<?php

declare(strict_types=1);

namespace App\Modules\Seguridad\Infrastructure\Persistence\Repositories;

use App\Modules\Seguridad\Domain\Contracts\UserRepositoryInterface;
use App\Modules\Seguridad\Domain\ValueObjects\AuthenticatedUser;
use App\Modules\Seguridad\Domain\ValueObjects\Rol;
use App\Modules\Seguridad\Infrastructure\Persistence\Models\UserAuthModel;
use Illuminate\Support\Facades\DB;

final readonly class EloquentUserRepository implements UserRepositoryInterface
{
    public function buscarPorUsername(string $username): ?AuthenticatedUser
    {
        $user = UserAuthModel::where('username', $username)
            ->where('is_active', true)
            ->first();

        return $user instanceof UserAuthModel ? $this->toValueObject($user) : null;
    }

    public function buscarPorId(int $id): ?AuthenticatedUser
    {
        $user = UserAuthModel::where('id', $id)
            ->where('is_active', true)
            ->first();

        return $user instanceof UserAuthModel ? $this->toValueObject($user) : null;
    }

    private function toValueObject(UserAuthModel $user): AuthenticatedUser
    {
        return new AuthenticatedUser(
            id: (int) $user->id,
            username: (string) $user->username,
            email: (string) $user->email,
            role: $this->computeRole($user),
            ruc: null,
            passwordHash: (string) $user->password,
        );
    }

    private function computeRole(UserAuthModel $user): string
    {
        $esAdmin = (bool) ($user->is_superuser || $user->is_staff);
        $esInspector = ! $esAdmin && DB::table('usuarios_perfiles')
            ->where('user_id', $user->id)
            ->where('es_inspector', true)
            ->exists();

        return Rol::resolver($esAdmin, $esInspector)->value;
    }
}
