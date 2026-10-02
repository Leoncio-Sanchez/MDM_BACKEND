<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Usuario administrador inicial compatible con Django
        $hasher = new \App\Modules\Seguridad\Infrastructure\Hashing\DjangoPbkdf2Hasher();
        DB::table('auth_user')->updateOrInsert(
            ['id' => 1],
            [
                'username' => 'admin',
                'email' => 'admin@muni.gob.pe',
                'password' => $hasher->hash('admin123'),
                'role' => 'admin',
                'is_active' => true,
                'is_staff' => true,
                'is_superuser' => true,
            ]
        );

        if (DB::table('usuarios_perfiles')->where('user_id', 1)->doesntExist()) {
            DB::table('usuarios_perfiles')->insert([
                'user_id' => 1,
                'es_inspector' => false,
                'cargo' => 'Administrador General',
                'es_administrador' => true,
                'acceso_licencias' => true,
                'acceso_itse' => true,
                'acceso_expedientes' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
