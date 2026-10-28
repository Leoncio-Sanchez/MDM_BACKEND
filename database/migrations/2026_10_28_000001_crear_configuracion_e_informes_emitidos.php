<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tablas nuevas (no existían en el sistema anterior):
 *
 * - configuracion_sistema: valores editables desde Administración sin tocar código
 *   (p. ej. el "Nombre Oficial del Año", que cambia cada enero).
 * - informes_emitidos: correlativo de los informes formales en PDF
 *   (INFORME N° 012-2026-MDM/GDES). Un periodo conserva su número al reimprimirse.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE IF NOT EXISTS configuracion_sistema (
    clave VARCHAR(60) PRIMARY KEY,
    valor TEXT NOT NULL DEFAULT '',
    fecha_actualizacion TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    usuario_id INTEGER NULL REFERENCES auth_user (id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS informes_emitidos (
    id BIGSERIAL PRIMARY KEY,
    tipo VARCHAR(40) NOT NULL,
    anio SMALLINT NOT NULL,
    numero INTEGER NOT NULL CHECK (numero > 0),
    periodo_desde DATE NOT NULL,
    periodo_hasta DATE NOT NULL,
    usuario_id INTEGER NOT NULL REFERENCES auth_user (id) ON DELETE RESTRICT,
    fecha_emision TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    CONSTRAINT informes_emitidos_numero_unico UNIQUE (tipo, anio, numero),
    CONSTRAINT informes_emitidos_periodo_unico UNIQUE (tipo, periodo_desde, periodo_hasta),
    CONSTRAINT informes_emitidos_periodo_valido CHECK (periodo_hasta >= periodo_desde)
);
SQL);

        // Valores iniciales (editables desde Administración → Configuración)
        $iniciales = [
            'informe.entidad' => 'MUNICIPALIDAD DISTRITAL DE MORALES - PROVINCIA DE SAN MARTÍN',
            'informe.nombre_anio' => '',
            'informe.gerencia' => 'Gerencia de Desarrollo Económico y Social',
            'informe.siglas' => 'MDM/GDES',
            'informe.firmante_nombre' => '',
            'informe.firmante_cargo' => 'Subgerente de Desarrollo Económico',
        ];
        foreach ($iniciales as $clave => $valor) {
            DB::table('configuracion_sistema')->insertOrIgnore(['clave' => $clave, 'valor' => $valor]);
        }
    }

    public function down(): void
    {
        DB::unprepared('DROP TABLE IF EXISTS informes_emitidos; DROP TABLE IF EXISTS configuracion_sistema;');
    }
};
