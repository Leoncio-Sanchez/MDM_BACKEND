<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Cuentas de ciudadanos: el administrado crea su cuenta con su DNI/RUC y el número de uno de
 * sus expedientes (la misma prueba que pide la consulta pública) y, al entrar, ve todos sus
 * trámites. La cuenta se ata al documento y no a una fila de "personas": así ve todo lo que
 * está a su nombre aunque la persona se haya registrado más de una vez.
 *
 * El usuario (auth_user) es "ciu_<documento>", no es personal (is_staff = false) y no tiene
 * perfil de accesos: su rol es CIUDADANO.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            CREATE TABLE ciudadanos_cuentas (
                user_id INTEGER PRIMARY KEY REFERENCES auth_user(id) ON DELETE RESTRICT,
                documento VARCHAR(12) NOT NULL UNIQUE CHECK (documento ~ '^[0-9A-Z]{8,12}$'),
                fecha_registro TIMESTAMPTZ NOT NULL DEFAULT NOW(),
                -- Última vez que restableció su contraseña (con su documento y un expediente)
                fecha_restablecimiento TIMESTAMPTZ
            )
            SQL);
    }

    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS ciudadanos_cuentas');
    }
};
