<?php

declare(strict_types=1);

namespace App\Modules\Inspectores\Infrastructure\Persistence\Repositories;

use App\Modules\Inspectores\Domain\Contracts\InspectorRepositoryInterface;
use App\Modules\Usuarios\Infrastructure\Persistence\Models\UsuarioModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

final class EloquentInspectorRepository implements InspectorRepositoryInterface
{
    /**
     * @return array<int, UsuarioModel>
     */
    public function listar(?string $busqueda = null): array
    {
        $query = UsuarioModel::with('perfil')
            ->where('is_active', true)
            ->orderBy('username', 'asc');

        if ($busqueda !== null && trim($busqueda) !== '') {
            $like = DB::connection()->getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';
            $term = '%'.trim($busqueda).'%';
            $query->where(function ($q) use ($term, $like): void {
                $q->where('username', $like, $term)
                    ->orWhere('first_name', $like, $term)
                    ->orWhere('last_name', $like, $term);
            });
        }

        return $query->get()->all();
    }

    public function buscarPorId(int $id): ?UsuarioModel
    {
        return UsuarioModel::with('perfil')->find($id);
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    public function crear(array $datos): UsuarioModel
    {
        return DB::transaction(function () use ($datos): UsuarioModel {
            $user = new UsuarioModel;
            $user->username = (string) $datos['username'];
            $user->password = (string) ($datos['password'] ?? '');
            $user->first_name = (string) ($datos['first_name'] ?? '');
            $user->last_name = (string) ($datos['last_name'] ?? '');
            $user->email = (string) ($datos['email'] ?? '');
            $user->is_active = (bool) ($datos['is_active'] ?? true);
            $user->is_staff = (bool) ($datos['is_staff'] ?? false);
            $user->is_superuser = false;
            $user->date_joined = Carbon::now('UTC')->toDateTimeString();
            $user->save();

            $user->load('perfil');

            return $user;
        });
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    public function actualizar(int $id, array $datos): ?UsuarioModel
    {
        $user = UsuarioModel::with('perfil')->find($id);
        if (! $user) {
            return null;
        }

        if (array_key_exists('first_name', $datos)) {
            $user->first_name = (string) $datos['first_name'];
        }
        if (array_key_exists('last_name', $datos)) {
            $user->last_name = (string) $datos['last_name'];
        }
        if (array_key_exists('email', $datos)) {
            $user->email = (string) $datos['email'];
        }
        if (array_key_exists('is_active', $datos)) {
            $user->is_active = (bool) $datos['is_active'];
        }
        $user->save();

        return $user;
    }

    public function eliminar(int $id): bool
    {
        $user = UsuarioModel::find($id);
        if (! $user) {
            return false;
        }

        return (bool) $user->delete();
    }
}
