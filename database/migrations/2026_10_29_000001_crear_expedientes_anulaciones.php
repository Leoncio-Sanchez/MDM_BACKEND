<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Los expedientes ya no se eliminan (LPAG, Ley 27444): se anulan dejando constancia de
 * motivo, fecha y usuario. Tabla aparte para no alterar "expedientes" (esquema heredado).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            CREATE TABLE expedientes_anulaciones (
                id BIGSERIAL PRIMARY KEY,
                -- Una sola anulación por expediente; RESTRICT: la constancia no se pierde
                expediente_id BIGINT NOT NULL UNIQUE REFERENCES expedientes(id) ON DELETE RESTRICT,
                motivo VARCHAR(500) NOT NULL CHECK (LENGTH(TRIM(motivo)) >= 10),
                -- Resolución u otro documento que sustenta la anulación (opcional)
                documento VARCHAR(100),
                fecha_anulacion TIMESTAMPTZ NOT NULL DEFAULT NOW(),
                usuario_id INTEGER NOT NULL REFERENCES auth_user(id) ON DELETE RESTRICT
            )
            SQL);
    }

    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS expedientes_anulaciones');
    }
};
