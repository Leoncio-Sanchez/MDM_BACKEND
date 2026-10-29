<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Varios inspectores por visita: el inspector de la asignación es el responsable (registra
 * y firma el acta) y puede ir con acompañantes.
 *
 * Quitar a un acompañante no borra la fila: queda con fecha y usuario. El acta guarda
 * quiénes acompañaron la visita, porque la asignación puede cambiar después.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            CREATE TABLE inspecciones_asignaciones_acompanantes (
                id BIGSERIAL PRIMARY KEY,
                asignacion_id BIGINT NOT NULL REFERENCES inspecciones_asignaciones(id) ON DELETE RESTRICT,
                inspector_id INTEGER NOT NULL REFERENCES auth_user(id) ON DELETE RESTRICT,
                fecha_agregado TIMESTAMPTZ NOT NULL DEFAULT NOW(),
                agregado_por INTEGER NOT NULL REFERENCES auth_user(id) ON DELETE RESTRICT,
                -- Se llenan al quitarlo del equipo; mientras estén vacíos acompaña la inspección
                fecha_retiro TIMESTAMPTZ,
                retirado_por INTEGER REFERENCES auth_user(id) ON DELETE RESTRICT,
                CONSTRAINT inspecciones_acompanantes_retiro_completo CHECK ((fecha_retiro IS NULL) = (retirado_por IS NULL))
            )
            SQL);

        DB::statement('CREATE UNIQUE INDEX inspecciones_acompanantes_vigente_unico ON inspecciones_asignaciones_acompanantes (asignacion_id, inspector_id) WHERE fecha_retiro IS NULL');
        // Bandeja del acompañante
        DB::statement('CREATE INDEX inspecciones_acompanantes_inspector_vigentes ON inspecciones_asignaciones_acompanantes (inspector_id) WHERE fecha_retiro IS NULL');

        DB::statement(<<<'SQL'
            CREATE TABLE inspecciones_actas_acompanantes (
                acta_id BIGINT NOT NULL REFERENCES inspecciones_actas(id) ON DELETE RESTRICT,
                inspector_id INTEGER NOT NULL REFERENCES auth_user(id) ON DELETE RESTRICT,
                PRIMARY KEY (acta_id, inspector_id)
            )
            SQL);

        DB::statement('CREATE INDEX inspecciones_actas_acompanantes_inspector ON inspecciones_actas_acompanantes (inspector_id)');
    }

    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS inspecciones_actas_acompanantes');
        DB::statement('DROP TABLE IF EXISTS inspecciones_asignaciones_acompanantes');
    }
};
