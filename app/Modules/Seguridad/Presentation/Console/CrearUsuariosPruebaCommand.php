<?php

declare(strict_types=1);

namespace App\Modules\Seguridad\Presentation\Console;

use App\Modules\Seguridad\Domain\Contracts\PasswordHasherInterface;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Un usuario por perfil (mesa de partes, licencias, ITSE, jefatura, inspectores, admin y
 * auditor) para probar el sistema o capacitar al personal. Todos se llaman "prueba_*" y
 * comparten una contraseña generada al azar en cada ejecución.
 *
 * No corre en producción: allí las cuentas se crean en Usuarios y Permisos.
 */
final class CrearUsuariosPruebaCommand extends Command
{
    protected $signature = 'usuarios:prueba
        {--eliminar : Borra los usuarios de prueba (o los desactiva si ya registraron datos)}
        {--archivo= : Además de mostrarlas, guarda las credenciales en este archivo}';

    protected $description = 'Crea (o reinicia) un usuario de prueba por cada perfil, con una contraseña nueva';

    /** usuario => [nombre, es personal de oficina, accesos del perfil] */
    public const PERFILES = [
        'prueba_mesa' => ['MESA DE PARTES', true, ['expedientes' => true]],
        'prueba_licencias' => ['OFICINA LICENCIAS', true, ['licencias' => true]],
        'prueba_itse' => ['OFICINA ITSE', true, ['itse' => true]],
        'prueba_jefe_itse' => ['JEFATURA ITSE', true, ['itse' => true, 'es_jefe_itse' => true]],
        'prueba_inspector1' => ['INSPECTOR UNO', false, ['es_inspector' => true]],
        'prueba_inspector2' => ['INSPECTOR DOS', false, ['es_inspector' => true]],
        'prueba_admin' => ['ADMINISTRADOR', true, ['admin' => true]],
        'prueba_auditor' => ['AUDITOR', true, ['reportes_completos' => true]],
    ];

    private const SIN_ACCESOS = [
        'expedientes' => false, 'licencias' => false, 'itse' => false, 'admin' => false,
        'es_inspector' => false, 'es_jefe_itse' => false, 'reportes_completos' => false,
    ];

    public function handle(PasswordHasherInterface $hasher): int
    {
        if (app()->environment('production')) {
            $this->error('No se crean usuarios de prueba en producción: use Usuarios y Permisos.');

            return self::FAILURE;
        }

        return $this->option('eliminar') ? $this->eliminar() : $this->crear($hasher);
    }

    private function crear(PasswordHasherInterface $hasher): int
    {
        // Sin caracteres que se confundan al dictarla (0/O, 1/l/I)
        $clave = substr(str_replace(['0', 'O', 'o', '1', 'l', 'I'], '', Str::password(24, symbols: false)), 0, 12);
        $hash = $hasher->hash($clave);

        DB::transaction(function () use ($hash): void {
            foreach (self::PERFILES as $usuario => [$nombre, $esPersonal, $accesos]) {
                $datos = [
                    'password' => $hash, 'first_name' => $nombre, 'last_name' => 'PRUEBA', 'email' => '',
                    'is_superuser' => false, 'is_staff' => $esPersonal, 'is_active' => true,
                ];
                $id = DB::table('auth_user')->where('username', $usuario)->value('id');
                if ($id === null) {
                    $id = DB::table('auth_user')->insertGetId($datos + ['username' => $usuario, 'date_joined' => now()]);
                } else {
                    DB::table('auth_user')->where('id', $id)->update($datos);
                }

                $perfil = array_merge(self::SIN_ACCESOS, $accesos);
                if (DB::table('usuarios_perfiles')->where('user_id', $id)->exists()) {
                    DB::table('usuarios_perfiles')->where('user_id', $id)->update($perfil);
                } else {
                    DB::table('usuarios_perfiles')->insert($perfil + ['user_id' => $id, 'fecha_digitacion' => now(), 'user_id_digitador' => $id]);
                }
            }
        });

        $filas = array_map(
            static fn (string $usuario, array $p): array => [$usuario, $p[0], self::describir($p[2])],
            array_keys(self::PERFILES),
            self::PERFILES,
        );
        $this->table(['Usuario', 'Nombre', 'Accesos'], $filas);
        $this->line("Contraseña (la misma para todos): {$clave}");

        $archivo = $this->option('archivo');
        if (is_string($archivo) && $archivo !== '') {
            $lineas = ['# Usuarios de prueba (local) — generados el '.now()->format('d/m/Y H:i'), '', "Contraseña para todos: `{$clave}`", '', '| Usuario | Perfil | Accesos |', '|---|---|---|'];
            foreach ($filas as [$usuario, $nombre, $accesos]) {
                $lineas[] = "| `{$usuario}` | {$nombre} | {$accesos} |";
            }
            $lineas[] = '';
            $lineas[] = 'Para borrarlos: `php artisan usuarios:prueba --eliminar`. Cada ejecución sin `--eliminar` genera una contraseña nueva.';
            file_put_contents($archivo, implode("\n", $lineas)."\n");
            $this->info("Credenciales guardadas en {$archivo}");
        }

        return self::SUCCESS;
    }

    private function eliminar(): int
    {
        foreach (array_keys(self::PERFILES) as $usuario) {
            $id = DB::table('auth_user')->where('username', $usuario)->value('id');
            if ($id === null) {
                continue;
            }
            try {
                DB::transaction(function () use ($id): void {
                    DB::table('usuarios_perfiles')->where('user_id', $id)->delete();
                    DB::table('auth_user')->where('id', $id)->delete();
                });
                $this->line("Eliminado: {$usuario}");
            } catch (QueryException) {
                // Registró expedientes, actas, etc.: nada se borra, solo se le quita el acceso
                DB::table('auth_user')->where('id', $id)->update(['is_active' => false]);
                $this->warn("Desactivado (tiene registros a su nombre): {$usuario}");
            }
        }

        return self::SUCCESS;
    }

    /** @param  array<string, bool>  $accesos */
    private static function describir(array $accesos): string
    {
        $nombres = ['expedientes' => 'expedientes', 'licencias' => 'licencias', 'itse' => 'itse', 'admin' => 'admin',
            'es_inspector' => 'inspector', 'es_jefe_itse' => 'jefe_itse', 'reportes_completos' => 'reportes'];

        return implode(', ', array_map(static fn (string $k): string => $nombres[$k], array_keys(array_filter($accesos))));
    }
}
