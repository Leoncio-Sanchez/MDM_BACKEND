<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Usuario;
use App\Modules\Seguridad\Domain\Contracts\PasswordHasherInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class InspectorService
{
    public function __construct(
        private readonly PasswordHasherInterface $hasher,
    ) {}

    /**
     * @return Collection<int, Usuario>
     */
    public function listar(?string $busqueda = null): Collection
    {
        $query = Usuario::with('perfil')
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

        return $query->get();
    }

    /**
     * @return Collection<int, Usuario>
     */
    public function buscar(?string $busqueda = null): Collection
    {
        return $this->listar($busqueda);
    }

    public function obtenerPorId(int $id): ?Usuario
    {
        return Usuario::with('perfil')->find($id);
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    public function crear(array $datos): Usuario
    {
        return DB::transaction(function () use ($datos): Usuario {
            $user = new Usuario;
            $user->username = (string) $datos['username'];
            $password = (string) ($datos['password'] ?? '');
            if ($password !== '') {
                $user->password = $this->hasher->hash($password);
            } else {
                $user->password = '';
            }
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
    public function actualizar(int $id, array $datos): ?Usuario
    {
        $user = Usuario::with('perfil')->find($id);
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
        $user = Usuario::find($id);
        if (! $user) {
            return false;
        }

        return (bool) $user->delete();
    }
}
