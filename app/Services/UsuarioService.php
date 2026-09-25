<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Usuario;
use App\Models\UsuarioPerfil;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class UsuarioService
{
    public function __construct(
        private readonly DjangoPbkdf2Hasher $hasher,
    ) {}

    /**
     * @return Collection<int, Usuario>
     */
    public function listar(?string $busqueda = null): Collection
    {
        $query = Usuario::with('perfil')->orderBy('username', 'asc');

        if ($busqueda !== null && trim($busqueda) !== '') {
            $term = '%'.trim($busqueda).'%';
            $op = DB::connection()->getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';
            $query->where(function ($q) use ($term, $op) {
                $q->where('username', $op, $term)
                    ->orWhere('first_name', $op, $term)
                    ->orWhere('last_name', $op, $term)
                    ->orWhere('email', $op, $term);
            });
        }

        return $query->get();
    }

    public function buscarPorId(int $id): ?Usuario
    {
        return Usuario::with('perfil')->find($id);
    }

    public function buscarPorUsername(string $username): ?Usuario
    {
        return Usuario::with('perfil')->where('username', $username)->first();
    }

    /**
     * @param  array<string, mixed>  $datos
     * @param  array<string, mixed>|null  $perfilDatos
     */
    public function crear(array $datos, ?array $perfilDatos = null, ?int $digitadorId = null): Usuario
    {
        return DB::transaction(function () use ($datos, $perfilDatos, $digitadorId) {
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
            $user->is_superuser = (bool) ($datos['is_superuser'] ?? false);
            $user->date_joined = now();
            $user->save();

            if ($perfilDatos !== null) {
                $perfil = new UsuarioPerfil;
                $perfil->user_id = $user->id;
                $perfil->expedientes = (bool) ($perfilDatos['expedientes'] ?? false);
                $perfil->licencias = (bool) ($perfilDatos['licencias'] ?? false);
                $perfil->itse = (bool) ($perfilDatos['itse'] ?? false);
                $perfil->admin = (bool) ($perfilDatos['admin'] ?? false);
                $perfil->user_id_digitador = $digitadorId ?? $user->id;
                $perfil->fecha_digitacion = now();
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
    public function actualizar(int $id, array $datos, ?array $perfilDatos = null, ?int $digitadorId = null): ?Usuario
    {
        return DB::transaction(function () use ($id, $datos, $perfilDatos, $digitadorId) {
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
            if (array_key_exists('is_staff', $datos)) {
                $user->is_staff = (bool) $datos['is_staff'];
            }
            $user->save();

            if ($perfilDatos !== null) {
                $perfil = UsuarioPerfil::where('user_id', $id)->first();
                if (! $perfil) {
                    $perfil = new UsuarioPerfil;
                    $perfil->user_id = $id;
                    $perfil->user_id_digitador = $digitadorId ?? $id;
                    $perfil->fecha_digitacion = now();
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

    public function eliminar(int $id): bool
    {
        $user = Usuario::find($id);
        if (! $user) {
            return false;
        }

        $user->is_active = false;

        return $user->save();
    }

    /**
     * @return array{success: bool, status: int, data: array<string, string>}
     */
    public function cambiarPassword(int $id, string $password, string $passwordConfirm): array
    {
        $password = trim($password);
        $passwordConfirm = trim($passwordConfirm);

        if ($password === '') {
            return [
                'success' => false,
                'status' => 400,
                'data' => ['error' => 'La contraseña no puede estar vacía.'],
            ];
        }

        if ($password !== $passwordConfirm) {
            return [
                'success' => false,
                'status' => 400,
                'data' => ['error' => 'Las contraseñas no coinciden.'],
            ];
        }

        if (mb_strlen($password) < 6) {
            return [
                'success' => false,
                'status' => 400,
                'data' => ['error' => 'La contraseña debe tener al menos 6 caracteres.'],
            ];
        }

        $user = Usuario::find($id);
        if (! $user) {
            return [
                'success' => false,
                'status' => 404,
                'data' => ['error' => 'Usuario no encontrado.'],
            ];
        }

        $user->password = $this->hasher->hash($password);
        $user->save();

        return [
            'success' => true,
            'status' => 200,
            'data' => ['mensaje' => 'Contraseña actualizada correctamente.'],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function construirMenus(int $userId): array
    {
        $opciones = $this->obtenerOpcionesUsuario($userId);

        $tieneCatalogo = (
            $opciones['expedientes']
            || $opciones['licencias']
            || $opciones['itse']
            || $opciones['admin']
        );

        $menus = [];

        // 1. Dashboard (siempre visible)
        $menus[] = [
            'id' => 'dashboard',
            'label' => 'Dashboard',
            'url' => '/dashboard',
            'submenues' => [],
        ];

        // 2. Expedientes
        if ($opciones['expedientes']) {
            $menus[] = [
                'id' => 'expedientes',
                'label' => 'Expedientes',
                'url' => '/expedientes',
                'submenues' => [],
            ];
        }

        // 3. Licencias de funcionamiento
        if ($opciones['licencias']) {
            $menus[] = [
                'id' => 'licencias-funcionamiento',
                'label' => 'Licencias de funcionamiento',
                'url' => '/licencias-funcionamiento',
                'submenues' => [],
            ];
        }

        // 4. Certificados ITSE
        if ($opciones['itse']) {
            $menus[] = [
                'id' => 'certificados-itse',
                'label' => 'Certificados ITSE',
                'url' => '/certificados-itse',
                'submenues' => [],
            ];
        }

        // 5. Reportes (siempre visible con sus submenús)
        $menus[] = [
            'id' => 'reportes',
            'label' => 'Reportes',
            'url' => '/reportes',
            'submenues' => [
                [
                    'id' => 'reportes-expedientes',
                    'label' => 'Expedientes',
                    'url' => '/reportes/expedientes',
                    'submenues' => [],
                ],
                [
                    'id' => 'reportes-licencias-funcionamiento',
                    'label' => 'Licencias de funcionamiento',
                    'url' => '/reportes/licencias-funcionamiento',
                    'submenues' => [],
                ],
                [
                    'id' => 'reportes-certificados-itse',
                    'label' => 'Certificados ITSE',
                    'url' => '/reportes/certificados-itse',
                    'submenues' => [],
                ],
            ],
        ];

        // 6. Catálogos
        if ($tieneCatalogo) {
            $menus[] = [
                'id' => 'catalogos',
                'label' => 'Catálogos',
                'url' => '/catalogos',
                'submenues' => [
                    [
                        'id' => 'catalogos-personas',
                        'label' => 'Personas',
                        'url' => '/catalogos/personas',
                        'submenues' => [],
                    ],
                    [
                        'id' => 'catalogos-giros',
                        'label' => 'Giros',
                        'url' => '/catalogos/giros',
                        'submenues' => [],
                    ],
                    [
                        'id' => 'catalogos-zonificaciones',
                        'label' => 'Zonificaciones',
                        'url' => '/catalogos/zonificaciones',
                        'submenues' => [],
                    ],
                    [
                        'id' => 'catalogos-tipos-procedimiento-tupa',
                        'label' => 'Tipo de procedimiento TUPA',
                        'url' => '/catalogos/tipos-procedimiento-tupa',
                        'submenues' => [],
                    ],
                ],
            ];
        }

        // 7. Usuarios
        if ($opciones['admin']) {
            $menus[] = [
                'id' => 'usuarios',
                'label' => 'Usuarios',
                'url' => '/usuarios',
                'submenues' => [],
            ];
        }

        return $menus;
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

        $user = Usuario::find($id);
        if (! $user || ! $user->is_active) {
            return $sinAcceso;
        }

        if ($user->is_superuser) {
            return [
                'expedientes' => true,
                'licencias' => true,
                'itse' => true,
                'admin' => true,
            ];
        }

        $perfil = UsuarioPerfil::where('user_id', $id)->first();
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
