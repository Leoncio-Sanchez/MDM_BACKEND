<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Estados\Infrastructure\Persistence\Models\EstadoModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Usuario para pruebas de API
        User::create([
            'name' => 'Admin User',
            'email' => 'admin',
            'password' => Hash::make('admin'),
        ]);

        // Estados mock
        EstadoModel::create(['nombre' => 'Registrado', 'es_para_lf' => true, 'es_para_itse' => true, 'esta_activo' => true]);
        EstadoModel::create(['nombre' => 'En Proceso', 'es_para_lf' => true, 'es_para_itse' => true, 'esta_activo' => true]);
        EstadoModel::create(['nombre' => 'Observado', 'es_para_lf' => true, 'es_para_itse' => false, 'esta_activo' => false]);
    }
}
