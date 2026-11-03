<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Seguridad\Infrastructure\Hashing\DjangoPbkdf2Hasher;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    /** Datos para desarrollo local: administrador + catálogos reales. */
    public function run(): void
    {
        if (app()->environment('production')) {
            throw new RuntimeException(
                'DatabaseSeeder crea un administrador con contraseña conocida: no corre en producción. '
                .'Use usuarios:administrador y luego db:seed --class=CatalogosSeeder (ver DEPLOY.md).',
            );
        }

        // Administrador local (contraseña de desarrollo: admin123). Contraseña en formato Django.
        DB::table('auth_user')->updateOrInsert(
            ['id' => 1],
            [
                'username' => 'admin',
                'password' => (new DjangoPbkdf2Hasher())->hash('admin123'),
                'first_name' => '',
                'last_name' => '',
                'email' => 'admin@muni.gob.pe',
                'is_superuser' => true,
                'is_staff' => true,
                'is_active' => true,
                'date_joined' => now(),
            ]
        );
        // Se insertó con id explícito: avanzar la secuencia para que el próximo usuario no choque
        DB::statement("SELECT setval(pg_get_serial_sequence('auth_user', 'id'), (SELECT MAX(id) FROM auth_user))");

        // Perfil de accesos (columnas reales: expedientes, licencias, itse, admin)
        DB::table('usuarios_perfiles')->updateOrInsert(
            ['user_id' => 1],
            [
                'expedientes' => true,
                'licencias' => true,
                'itse' => true,
                'admin' => true,
                'fecha_digitacion' => now(),
                'user_id_digitador' => 1,
            ]
        );

        $this->call(CatalogosSeeder::class);
    }
}
