<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Los archivos adjuntos ya no se borran (ni el registro ni el archivo en disco): se inactivan
 * con motivo, fecha y usuario, por si el OCI audita. Una tabla por cada tabla de archivos
 * (con su FK) para no alterar las tablas heredadas.
 */
return new class extends Migration
{
    private const TABLAS = ['expedientes_archivos', 'itse_archivos', 'licencias_funcionamiento_archivos'];

    public function up(): void
    {
        foreach (self::TABLAS as $tabla) {
            DB::statement(<<<SQL
                CREATE TABLE {$tabla}_inactivaciones (
                    id BIGSERIAL PRIMARY KEY,
                    -- Una sola inactivación por archivo; RESTRICT: ni el archivo ni la constancia se borran
                    archivo_id BIGINT NOT NULL UNIQUE REFERENCES {$tabla}(id) ON DELETE RESTRICT,
                    motivo VARCHAR(500) NOT NULL CHECK (LENGTH(TRIM(motivo)) >= 10),
                    fecha_inactivacion TIMESTAMPTZ NOT NULL DEFAULT NOW(),
                    usuario_id INTEGER NOT NULL REFERENCES auth_user(id) ON DELETE RESTRICT
                )
                SQL);
        }
    }

    public function down(): void
    {
        foreach (self::TABLAS as $tabla) {
            DB::statement("DROP TABLE IF EXISTS {$tabla}_inactivaciones");
        }
    }
};
