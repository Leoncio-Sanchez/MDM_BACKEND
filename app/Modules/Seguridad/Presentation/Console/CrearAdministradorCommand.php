<?php

declare(strict_types=1);

namespace App\Modules\Seguridad\Presentation\Console;

use App\Modules\Seguridad\Domain\Contracts\PasswordHasherInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Primer administrador de una instalación nueva (o uno adicional), con contraseña propia.
 * Reemplaza en producción al usuario "admin" de desarrollo que crea DatabaseSeeder.
 */
final class CrearAdministradorCommand extends Command
{
    public const LARGO_MINIMO = 12;

    protected $signature = 'usuarios:administrador {usuario : Nombre de usuario} {--nombre= : Nombres y apellidos}';

    protected $description = 'Crea un superusuario con todos los accesos; pide la contraseña sin mostrarla';

    public function handle(PasswordHasherInterface $hasher): int
    {
        $usuario = trim((string) $this->argument('usuario'));
        if ($usuario === '' || DB::table('auth_user')->where('username', $usuario)->exists()) {
            $this->error("El usuario «{$usuario}» ya existe o no es válido.");

            return self::FAILURE;
        }

        $clave = (string) $this->secret('Contraseña (mínimo '.self::LARGO_MINIMO.' caracteres)');
        if (mb_strlen($clave) < self::LARGO_MINIMO) {
            $this->error('La contraseña es demasiado corta.');

            return self::FAILURE;
        }
        if ($clave !== (string) $this->secret('Repita la contraseña')) {
            $this->error('Las contraseñas no coinciden.');

            return self::FAILURE;
        }

        $id = DB::transaction(function () use ($usuario, $clave, $hasher): int {
            $id = (int) DB::table('auth_user')->insertGetId([
                'username' => $usuario, 'password' => $hasher->hash($clave),
                'first_name' => mb_strtoupper(trim((string) $this->option('nombre'))), 'last_name' => '', 'email' => '',
                'is_superuser' => true, 'is_staff' => true, 'is_active' => true, 'date_joined' => now(),
            ]);
            DB::table('usuarios_perfiles')->insert([
                'expedientes' => true, 'licencias' => true, 'itse' => true, 'admin' => true,
                'es_inspector' => false, 'es_jefe_itse' => true, 'reportes_completos' => true,
                'user_id' => $id, 'fecha_digitacion' => now(), 'user_id_digitador' => $id,
            ]);

            return $id;
        });

        $this->info("Administrador «{$usuario}» creado (id {$id}).");

        return self::SUCCESS;
    }
}
