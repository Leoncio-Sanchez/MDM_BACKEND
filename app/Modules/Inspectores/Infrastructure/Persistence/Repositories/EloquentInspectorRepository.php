<?php

declare(strict_types=1);

namespace App\Modules\Inspectores\Infrastructure\Persistence\Repositories;

use App\Modules\Inspectores\Domain\Contracts\InspectorRepositoryInterface;
use App\Modules\Usuarios\Infrastructure\Persistence\Models\UsuarioModel;
use App\Modules\Usuarios\Infrastructure\Persistence\Models\UsuarioPerfilModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Los inspectores son usuarios con la casilla "es_inspector" en su perfil. Este módulo solo
 * ve y modifica a esos usuarios: antes listaba (y podía borrar) a cualquier usuario del sistema.
 */
final class EloquentInspectorRepository implements InspectorRepositoryInterface
{
    /**
     * @return array<int, UsuarioModel>
     */
    public function listar(?string $busqueda = null): array
    {
        $query = $this->inspectores()
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
        return $this->inspectores()->find($id);
    }

    /**
     * Crea la cuenta y su perfil de inspector (sin otros accesos).
     *
     * @param  array<string, mixed>  $datos
     */
    public function crear(array $datos, int $digitadorId): UsuarioModel
    {
        return DB::transaction(function () use ($datos, $digitadorId): UsuarioModel {
            $user = new UsuarioModel;
            $user->username = (string) $datos['username'];
            $user->password = (string) ($datos['password'] ?? '');
            $user->first_name = (string) ($datos['first_name'] ?? '');
            $user->last_name = (string) ($datos['last_name'] ?? '');
            $user->email = (string) ($datos['email'] ?? '');
            $user->is_active = (bool) ($datos['is_active'] ?? true);
            // Personal de campo: no entra al panel administrativo
            $user->is_staff = false;
            $user->is_superuser = false;
            $user->date_joined = Carbon::now('UTC')->toDateTimeString();
            $user->save();

            $perfil = new UsuarioPerfilModel;
            $perfil->user_id = $user->id;
            $perfil->expedientes = false;
            $perfil->licencias = false;
            $perfil->itse = false;
            $perfil->admin = false;
            $perfil->es_inspector = true;
            $perfil->es_jefe_itse = false;
            $perfil->reportes_completos = false;
            $perfil->user_id_digitador = $digitadorId;
            $perfil->fecha_digitacion = Carbon::now('UTC')->toDateTimeString();
            $perfil->save();

            $user->load('perfil');

            return $user;
        });
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    public function actualizar(int $id, array $datos): ?UsuarioModel
    {
        $user = $this->buscarPorId($id);
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

    /** Los usuarios no se borran: la cuenta queda inactiva (no puede iniciar sesión) */
    public function desactivar(int $id): bool
    {
        $user = $this->buscarPorId($id);
        if (! $user) {
            return false;
        }

        $user->is_active = false;

        return $user->save();
    }

    /** @return Builder<UsuarioModel> */
    private function inspectores(): Builder
    {
        return UsuarioModel::with('perfil')
            ->whereHas('perfil', fn (Builder $p) => $p->where('es_inspector', true));
    }
}
