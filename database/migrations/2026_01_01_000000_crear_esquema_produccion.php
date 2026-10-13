<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Crea las tablas del negocio con el esquema real de producción (PostgreSQL).
 *
 * El SQL vive en database/sql/esquema_produccion.sql para poder revisarlo y compararlo
 * con la base del sistema actual. Si las tablas ya existen (base importada desde el
 * sistema anterior) la migración no hace nada.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('expedientes')) {
            return;
        }

        DB::unprepared((string) file_get_contents(database_path('sql/esquema_produccion.sql')));
    }

    public function down(): void
    {
        // Orden inverso de dependencias (las FKs impiden borrar primero las tablas padre)
        $tablas = [
            'licencias_funcionamiento_giros', 'licencias_funcionamiento_estados', 'licencias_funcionamiento_archivos',
            'licencias_funcionamiento', 'itse_giros', 'itse_estados', 'itse_archivos', 'itse',
            'autorizaciones_improcedentes', 'expedientes_archivos', 'expedientes', 'usuarios_perfiles',
            'personas_documentos', 'personas', 'feriados_recurrentes', 'feriados_anuales',
            'tipos_procedimiento_tupa', 'tipos_licencia', 'tipos_documento_identidad', 'zonificaciones',
            'niveles_riesgo', 'giros', 'estados', 'unidades_organicas', 'auth_user',
        ];
        foreach ($tablas as $tabla) {
            Schema::dropIfExists($tabla);
        }
    }
};
