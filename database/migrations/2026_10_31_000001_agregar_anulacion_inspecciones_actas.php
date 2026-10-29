<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Un acta registrada con un error no se corrige ni se borra: el jefe de ITSE la anula
 * dejando constancia (motivo, documento, fecha y quién) y el inspector registra la
 * correcta. El acta anulada se conserva con sus fotos, pero ya no cuenta como visita.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            ALTER TABLE inspecciones_actas
                ADD COLUMN fecha_anulacion TIMESTAMPTZ,
                ADD COLUMN motivo_anulacion VARCHAR(500),
                -- Memorando u otro documento que sustenta la anulación (opcional)
                ADD COLUMN documento_anulacion VARCHAR(100),
                ADD COLUMN anulado_por INTEGER REFERENCES auth_user(id) ON DELETE RESTRICT,
                ADD CONSTRAINT inspecciones_actas_anulacion_completa CHECK (
                    (fecha_anulacion IS NULL AND motivo_anulacion IS NULL AND documento_anulacion IS NULL AND anulado_por IS NULL)
                    OR (fecha_anulacion IS NOT NULL AND anulado_por IS NOT NULL AND LENGTH(TRIM(motivo_anulacion)) >= 10)
                )
            SQL);

        // La visita y el número de acta solo se reservan entre las actas vigentes: así el
        // inspector puede volver a registrar la visita (incluso con el mismo número del talonario)
        DB::statement('ALTER TABLE inspecciones_actas DROP CONSTRAINT inspecciones_actas_una_por_visita');
        DB::statement('CREATE UNIQUE INDEX inspecciones_actas_una_por_visita ON inspecciones_actas (expediente_id, visita) WHERE fecha_anulacion IS NULL');
        DB::statement('DROP INDEX inspecciones_actas_numero_unico');
        DB::statement('CREATE UNIQUE INDEX inspecciones_actas_numero_unico ON inspecciones_actas (UPPER(numero_acta)) WHERE fecha_anulacion IS NULL');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX inspecciones_actas_numero_unico');
        DB::statement('CREATE UNIQUE INDEX inspecciones_actas_numero_unico ON inspecciones_actas (UPPER(numero_acta))');
        DB::statement('DROP INDEX inspecciones_actas_una_por_visita');
        DB::statement('ALTER TABLE inspecciones_actas ADD CONSTRAINT inspecciones_actas_una_por_visita UNIQUE (expediente_id, visita)');
        DB::statement(<<<'SQL'
            ALTER TABLE inspecciones_actas
                DROP CONSTRAINT inspecciones_actas_anulacion_completa,
                DROP COLUMN anulado_por,
                DROP COLUMN documento_anulacion,
                DROP COLUMN motivo_anulacion,
                DROP COLUMN fecha_anulacion
            SQL);
    }
};
