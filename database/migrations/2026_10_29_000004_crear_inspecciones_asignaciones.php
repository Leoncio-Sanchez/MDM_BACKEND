<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Asignación de inspectores a los expedientes que esperan su inspección ITSE.
 *
 * Se asigna el expediente y no la tabla "itse", porque el certificado ITSE se registra
 * después de la inspección. Reasignar no borra nada: la asignación anterior queda
 * cerrada con fecha, usuario y motivo, y se abre una nueva.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            CREATE TABLE inspecciones_asignaciones (
                id BIGSERIAL PRIMARY KEY,
                expediente_id BIGINT NOT NULL REFERENCES expedientes(id) ON DELETE RESTRICT,
                inspector_id INTEGER NOT NULL REFERENCES auth_user(id) ON DELETE RESTRICT,
                -- Local a inspeccionar: se propone desde la licencia o el solicitante y el jefe lo corrige
                nombre_comercial VARCHAR(250) NOT NULL CHECK (LENGTH(TRIM(nombre_comercial)) > 0),
                direccion VARCHAR(250) NOT NULL CHECK (LENGTH(TRIM(direccion)) > 0),
                fecha_programada DATE,
                indicaciones VARCHAR(500),
                fecha_asignacion TIMESTAMPTZ NOT NULL DEFAULT NOW(),
                asignado_por INTEGER NOT NULL REFERENCES auth_user(id) ON DELETE RESTRICT,
                -- Se llenan al reasignar; mientras estén vacíos la asignación está vigente
                fecha_reasignacion TIMESTAMPTZ,
                reasignado_por INTEGER REFERENCES auth_user(id) ON DELETE RESTRICT,
                motivo_reasignacion VARCHAR(500),
                CONSTRAINT inspecciones_asignaciones_cierre_completo CHECK (
                    (fecha_reasignacion IS NULL AND reasignado_por IS NULL AND motivo_reasignacion IS NULL)
                    OR (fecha_reasignacion IS NOT NULL AND reasignado_por IS NOT NULL
                        AND LENGTH(TRIM(motivo_reasignacion)) >= 10)
                )
            )
            SQL);

        // Un solo inspector vigente por expediente
        DB::statement('CREATE UNIQUE INDEX inspecciones_asignaciones_vigente_unica ON inspecciones_asignaciones (expediente_id) WHERE fecha_reasignacion IS NULL');
        // Bandeja del inspector
        DB::statement('CREATE INDEX inspecciones_asignaciones_inspector_vigentes ON inspecciones_asignaciones (inspector_id) WHERE fecha_reasignacion IS NULL');
    }

    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS inspecciones_asignaciones');
    }
};
