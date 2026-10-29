<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Acta de inspección ITSE (D.S. N° 002-2018-PCM) que el inspector registra en campo.
 *
 * Primera visita: Aprobado u Observado (con plazo de subsanación en días hábiles).
 * Reinspección (2.ª visita): Aprobado o Desaprobado. El acta no se modifica ni se borra:
 * es el sustento ante reclamos o auditorías; por eso las fotos también se guardan aparte.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            CREATE TABLE inspecciones_actas (
                id BIGSERIAL PRIMARY KEY,
                expediente_id BIGINT NOT NULL REFERENCES expedientes(id) ON DELETE RESTRICT,
                asignacion_id BIGINT NOT NULL REFERENCES inspecciones_asignaciones(id) ON DELETE RESTRICT,
                inspector_id INTEGER NOT NULL REFERENCES auth_user(id) ON DELETE RESTRICT,
                numero_acta VARCHAR(30) NOT NULL CHECK (LENGTH(TRIM(numero_acta)) > 0),
                visita SMALLINT NOT NULL CHECK (visita IN (1, 2)),
                fecha_visita TIMESTAMPTZ NOT NULL,
                area_m2 NUMERIC(18, 2) NOT NULL CHECK (area_m2 > 0),
                aforo INTEGER NOT NULL CHECK (aforo >= 0),
                extintores VARCHAR(10) NOT NULL CHECK (extintores IN ('CUMPLE', 'NO_CUMPLE', 'NO_APLICA')),
                senalizacion VARCHAR(10) NOT NULL CHECK (senalizacion IN ('CUMPLE', 'NO_CUMPLE', 'NO_APLICA')),
                luces_emergencia VARCHAR(10) NOT NULL CHECK (luces_emergencia IN ('CUMPLE', 'NO_CUMPLE', 'NO_APLICA')),
                tableros_electricos VARCHAR(10) NOT NULL CHECK (tableros_electricos IN ('CUMPLE', 'NO_CUMPLE', 'NO_APLICA')),
                observaciones TEXT,
                resultado VARCHAR(12) NOT NULL CHECK (resultado IN ('APROBADO', 'OBSERVADO', 'DESAPROBADO')),
                fecha_limite_subsanacion DATE,
                fecha_registro TIMESTAMPTZ NOT NULL DEFAULT NOW(),
                -- Observado solo en la 1.ª visita y siempre con plazo; Desaprobado solo en la reinspección
                CONSTRAINT inspecciones_actas_plazo_si_observado CHECK ((resultado = 'OBSERVADO') = (fecha_limite_subsanacion IS NOT NULL)),
                CONSTRAINT inspecciones_actas_observado_primera CHECK (resultado <> 'OBSERVADO' OR visita = 1),
                CONSTRAINT inspecciones_actas_desaprobado_segunda CHECK (resultado <> 'DESAPROBADO' OR visita = 2),
                CONSTRAINT inspecciones_actas_una_por_visita UNIQUE (expediente_id, visita)
            )
            SQL);

        DB::statement('CREATE UNIQUE INDEX inspecciones_actas_numero_unico ON inspecciones_actas (UPPER(numero_acta))');

        DB::statement(<<<'SQL'
            CREATE TABLE inspecciones_actas_fotos (
                id BIGSERIAL PRIMARY KEY,
                acta_id BIGINT NOT NULL REFERENCES inspecciones_actas(id) ON DELETE RESTRICT,
                tipo VARCHAR(12) NOT NULL CHECK (tipo IN ('FACHADA', 'EXTINTORES', 'TABLEROS', 'OTRA')),
                uuid UUID NOT NULL UNIQUE,
                nombre_original VARCHAR(255) NOT NULL,
                ruta_archivo VARCHAR(500) NOT NULL,
                extension VARCHAR(10) NOT NULL,
                tamanio_bytes BIGINT NOT NULL,
                fecha_registro TIMESTAMPTZ NOT NULL DEFAULT NOW()
            )
            SQL);

        DB::statement('CREATE INDEX inspecciones_actas_fotos_acta ON inspecciones_actas_fotos (acta_id)');
    }

    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS inspecciones_actas_fotos');
        DB::statement('DROP TABLE IF EXISTS inspecciones_actas');
    }
};
