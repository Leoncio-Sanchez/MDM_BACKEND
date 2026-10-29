<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Casillas nuevas del perfil (acordadas con el equipo):
 * - es_inspector: inspector de campo; solo ve y llena los locales que se le asignan.
 * - es_jefe_itse: jefatura de ITSE; asigna inspectores y administra sus cuentas (requiere el acceso ITSE).
 * - reportes_completos: solo lectura y descarga de todos los reportes (Subgerente, Auditor).
 * Con valor por defecto FALSE: los perfiles existentes no cambian.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            ALTER TABLE usuarios_perfiles
                ADD COLUMN es_inspector BOOLEAN NOT NULL DEFAULT FALSE,
                ADD COLUMN es_jefe_itse BOOLEAN NOT NULL DEFAULT FALSE,
                ADD COLUMN reportes_completos BOOLEAN NOT NULL DEFAULT FALSE,
                ADD CONSTRAINT usuarios_perfiles_jefe_itse_requiere_itse CHECK (NOT es_jefe_itse OR itse)
            SQL);
    }

    public function down(): void
    {
        DB::statement(<<<'SQL'
            ALTER TABLE usuarios_perfiles
                DROP CONSTRAINT IF EXISTS usuarios_perfiles_jefe_itse_requiere_itse,
                DROP COLUMN IF EXISTS es_inspector,
                DROP COLUMN IF EXISTS es_jefe_itse,
                DROP COLUMN IF EXISTS reportes_completos
            SQL);
    }
};
