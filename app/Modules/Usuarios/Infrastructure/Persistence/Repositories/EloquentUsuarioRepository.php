<?php

declare(strict_types=1);

namespace App\Modules\Usuarios\Infrastructure\Persistence\Repositories;

use App\Modules\Usuarios\Domain\Contracts\UsuarioRepositoryInterface;
use App\Modules\Usuarios\Infrastructure\Persistence\Models\UsuarioModel;
use App\Modules\Usuarios\Infrastructure\Persistence\Models\UsuarioPerfilModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

final class EloquentUsuarioRepository implements UsuarioRepositoryInterface
{
    /**
     * @return array<int, UsuarioModel>
     */
    public function listar(?string $busqueda = null): array
    {
        $query = UsuarioModel::with('perfil')->orderBy('username', 'asc');

        if ($busqueda !== null && trim($busqueda) !== '') {
            $term = '%'.trim($busqueda).'%';
            $query->where(function ($q) use ($term) {
                $q->where('username', 'ILIKE', $term)
                    ->orWhere('first_name', 'ILIKE', $term)
                    ->orWhere('last_name', 'ILIKE', $term)
                    ->orWhere('email', 'ILIKE', $term);
            });
        }

        return $query->get()->all();
    }

    public function buscarPorId(int $id): ?UsuarioModel
    {
        return UsuarioModel::with('perfil')->find($id);
    }

    public function buscarPorUsername(string $username): ?UsuarioModel
    {
        return UsuarioModel::with('perfil')->where('username', $username)->first();
    }

    /**
     * @param  array<string, mixed>  $datos
     * @param  array<string, mixed>|null  $perfilDatos
     */
    public function crear(array $datos, ?array $perfilDatos = null, ?int $digitadorId = null): UsuarioModel
    {
        return DB::transaction(function () use ($datos, $perfilDatos, $digitadorId) {
            $user = new UsuarioModel;
            $user->username = (string) $datos['username'];
            $user->password = (string) ($datos['password'] ?? '');
            $user->first_name = (string) ($datos['first_name'] ?? '');
            $user->last_name = (string) ($datos['last_name'] ?? '');
            $user->email = (string) ($datos['email'] ?? '');
            $user->is_active = (bool) ($datos['is_active'] ?? true);
            $user->is_staff = (bool) ($datos['is_staff'] ?? false);
            $user->is_superuser = (bool) ($datos['is_superuser'] ?? false);
            $user->date_joined = Carbon::now('UTC')->toDateTimeString();
            $user->save();

            if ($perfilDatos !== null) {
                $perfil = new UsuarioPerfilModel;
                $perfil->user_id = $user->id;
                $perfil->expedientes = (bool) ($perfilDatos['expedientes'] ?? false);
                $perfil->licencias = (bool) ($perfilDatos['licencias'] ?? false);
                $perfil->itse = (bool) ($perfilDatos['itse'] ?? false);
                $perfil->admin = (bool) ($perfilDatos['admin'] ?? false);
                $perfil->user_id_digitador = $digitadorId ?? $user->id;
                $perfil->fecha_digitacion = Carbon::now('UTC')->toDateTimeString();
                $perfil->save();
            }

            $user->load('perfil');

            return $user;
        });
    }

    /**
     * @param  array<string, mixed>  $datos
     * @param  array<string, mixed>|null  $perfilDatos
     */
    public function actualizar(int $id, array $datos, ?array $perfilDatos = null, ?int $digitadorId = null): ?UsuarioModel
    {
        return DB::transaction(function () use ($id, $datos, $perfilDatos, $digitadorId) {
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
            if (array_key_exists('is_staff', $datos)) {
                $user->is_staff = (bool) $datos['is_staff'];
            }
            $user->save();

            if ($perfilDatos !== null) {
                $perfil = UsuarioPerfilModel::where('user_id', $id)->first();
                if (! $perfil) {
                    $perfil = new UsuarioPerfilModel;
                    $perfil->user_id = $id;
                    $perfil->user_id_digitador = $digitadorId ?? $id;
                    $perfil->fecha_digitacion = Carbon::now('UTC')->toDateTimeString();
                }

                if (array_key_exists('expedientes', $perfilDatos)) {
                    $perfil->expedientes = (bool) $perfilDatos['expedientes'];
                }
                if (array_key_exists('licencias', $perfilDatos)) {
                    $perfil->licencias = (bool) $perfilDatos['licencias'];
                }
                if (array_key_exists('itse', $perfilDatos)) {
                    $perfil->itse = (bool) $perfilDatos['itse'];
                }
                if (array_key_exists('admin', $perfilDatos)) {
                    $perfil->admin = (bool) $perfilDatos['admin'];
                }
                $perfil->save();
            }

            return $user->fresh(['perfil']);
        });
    }

    public function desactivar(int $id): bool
    {
        $user = UsuarioModel::find($id);
        if (! $user) {
            return false;
        }

        $user->is_active = false;

        return $user->save();
    }

    public function actualizarPassword(int $id, string $hashedPassword): bool
    {
        $user = UsuarioModel::find($id);
        if (! $user) {
            return false;
        }

        $user->password = $hashedPassword;

        return $user->save();
    }

    /**
     * @return array{expedientes: bool, licencias: bool, itse: bool, admin: bool}
     */
    public function obtenerOpcionesUsuario(int $id): array
    {
        $sinAcceso = [
            'expedientes' => false,
            'licencias' => false,
            'itse' => false,
            'admin' => false,
        ];

        $user = UsuarioModel::find($id);
        if (! $user || ! $user->is_active) {
            return $sinAcceso;
        }

        // Si es superusuario, tiene acceso completo
        if ($user->is_superuser) {
            return [
                'expedientes' => true,
                'licencias' => true,
                'itse' => true,
                'admin' => true,
            ];
        }

        $perfil = UsuarioPerfilModel::where('user_id', $id)->first();
        if (! $perfil) {
            return $sinAcceso;
        }

        return [
            'expedientes' => (bool) $perfil->expedientes,
            'licencias' => (bool) $perfil->licencias,
            'itse' => (bool) $perfil->itse,
            'admin' => (bool) $perfil->admin,
        ];
    }
}
