<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Catálogos reales del sistema (giros CIIU, zonificaciones, procedimientos TUPA, feriados…).
 *
 * Los datos están en database/seeders/sql/catalogos.sql, generado desde el dump del sistema
 * anterior. Varios catálogos referencian al usuario id 1 (usuario_id / user_id), así que
 * DatabaseSeeder crea ese usuario antes.
 */
final class CatalogosSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('giros')->exists()) {
            $this->command?->warn('Los catálogos ya tienen datos: no se cargan de nuevo.');

            return;
        }

        DB::unprepared((string) file_get_contents(database_path('seeders/sql/catalogos.sql')));
    }
}
