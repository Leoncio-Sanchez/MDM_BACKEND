<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::unprepared(<<<'SQL'
CREATE OR REPLACE PROCEDURE sp_emitir_licencia_funcionamiento(
    IN p_uuid UUID,
    IN p_fecha_emision DATE,
    IN p_nombre_comercial VARCHAR(250),
    IN p_es_vigencia_indeterminada BOOLEAN,
    IN p_fecha_inicio_vigencia DATE,
    IN p_fecha_fin_vigencia DATE,
    IN p_actividad VARCHAR(50),
    IN p_direccion VARCHAR(250),
    IN p_hora_desde INTEGER,
    IN p_hora_hasta INTEGER,
    IN p_resolucion_numero VARCHAR(50),
    IN p_area NUMERIC(18,2),
    IN p_numero_recibo_pago VARCHAR(20),
    IN p_observaciones TEXT,
    IN p_se_puede_publicar BOOLEAN,
    IN p_expediente_id BIGINT,
    IN p_licencia_principal_id BIGINT,
    IN p_usuario_id INTEGER,
    IN p_nivel_riesgo_id BIGINT,
    IN p_conductor_id BIGINT,
    IN p_titular_id BIGINT,
    IN p_tipo_licencia_id BIGINT,
    IN p_zonificacion_id BIGINT,
    IN p_giros_json JSONB,
    INOUT p_licencia_id BIGINT DEFAULT NULL
)
LANGUAGE plpgsql
AS $$
DECLARE
    v_numero_licencia INTEGER;
    v_estado_vigente BIGINT;
    v_giro_id BIGINT;
    v_denegada BOOLEAN;
BEGIN
    -- 1. Cálculo transaccional atómico del correlativo oficial por año con bloqueo de concurrencia
    PERFORM 1 FROM licencias_funcionamiento WHERE EXTRACT(YEAR FROM fecha_emision) = EXTRACT(YEAR FROM p_fecha_emision) FOR UPDATE;

    SELECT COALESCE(MAX(numero_licencia), 0) + 1
    INTO v_numero_licencia
    FROM licencias_funcionamiento
    WHERE EXTRACT(YEAR FROM fecha_emision) = EXTRACT(YEAR FROM p_fecha_emision);

    -- 2. Resolver dinámicamente el ID del estado 'Vigente'
    SELECT id INTO v_estado_vigente
    FROM estados
    WHERE LOWER(nombre) = 'vigente'
    LIMIT 1;

    IF v_estado_vigente IS NULL THEN
        RAISE EXCEPTION 'Catálogo estados no tiene "Vigente".';
    END IF;

    -- 3. Validar que no exista el recibo de pago si fue provisto
    IF p_numero_recibo_pago IS NOT NULL AND TRIM(p_numero_recibo_pago) <> '' THEN
        IF EXISTS (SELECT 1 FROM licencias_funcionamiento WHERE numero_recibo_pago = p_numero_recibo_pago) THEN
            RAISE EXCEPTION 'Ya existe una licencia con el número de recibo de pago %.', p_numero_recibo_pago;
        END IF;
    END IF;

    -- 4. Validar improcedencias en expediente si aplica
    IF p_expediente_id IS NOT NULL THEN
        SELECT EXISTS (
            SELECT 1 FROM autorizaciones_improcedentes
            WHERE expediente_id = p_expediente_id AND tipo_autorizacion = 'LF'
        ) INTO v_denegada;

        IF v_denegada THEN
            RAISE EXCEPTION 'El expediente registra licencia denegada.';
        END IF;
    END IF;

    -- 5. Inserción atómica de la licencia con correlativo auto-generado
    INSERT INTO licencias_funcionamiento (
        uuid,
        numero_licencia,
        fecha_emision,
        nombre_comercial,
        es_vigencia_indeterminada,
        fecha_inicio_vigencia,
        fecha_fin_vigencia,
        actividad,
        direccion,
        hora_desde,
        hora_hasta,
        resolucion_numero,
        area,
        numero_recibo_pago,
        observaciones,
        se_puede_publicar,
        fecha_digitacion,
        expediente_id,
        licencia_principal_id,
        usuario_id,
        nivel_riesgo_id,
        conductor_id,
        titular_id,
        tipo_licencia_id,
        zonificacion_id
    ) VALUES (
        COALESCE(p_uuid, gen_random_uuid()),
        v_numero_licencia,
        p_fecha_emision,
        p_nombre_comercial,
        p_es_vigencia_indeterminada,
        p_fecha_inicio_vigencia,
        p_fecha_fin_vigencia,
        p_actividad,
        p_direccion,
        p_hora_desde,
        p_hora_hasta,
        p_resolucion_numero,
        p_area,
        p_numero_recibo_pago,
        p_observaciones,
        p_se_puede_publicar,
        NOW(),
        p_expediente_id,
        p_licencia_principal_id,
        p_usuario_id,
        p_nivel_riesgo_id,
        p_conductor_id,
        p_titular_id,
        p_tipo_licencia_id,
        p_zonificacion_id
    ) RETURNING id INTO p_licencia_id;

    -- 6. Inserción de giros asociados si existen
    IF p_giros_json IS NOT NULL AND jsonb_typeof(p_giros_json) = 'array' THEN
        FOR v_giro_id IN
            SELECT (val->>'giro_id')::bigint
            FROM jsonb_array_elements(p_giros_json) AS val
            WHERE val ? 'giro_id' AND (val->>'giro_id') ~ '^[0-9]+$'
            UNION
            SELECT val::text::bigint
            FROM jsonb_array_elements(p_giros_json) AS val
            WHERE jsonb_typeof(val) = 'number'
        LOOP
            INSERT INTO licencias_funcionamiento_giros (
                licencia_funcionamiento_id,
                giro_id,
                usuario_id,
                fecha_digitacion
            ) VALUES (
                p_licencia_id,
                v_giro_id,
                p_usuario_id,
                NOW()
            );
        END LOOP;
    END IF;

    -- 7. Registro inicial en bitácora de estados con el ID de estado dinámico
    INSERT INTO licencias_funcionamiento_estados (
        fecha_estado,
        documento,
        observaciones,
        fecha_digitacion,
        estado_id,
        licencia_funcionamiento_id,
        usuario_id
    ) VALUES (
        p_fecha_emision,
        COALESCE(p_resolucion_numero, 'RESOLUCION_INICIAL'),
        'Emisión de Licencia de Funcionamiento mediante Stored Procedure',
        NOW(),
        v_estado_vigente,
        p_licencia_id,
        p_usuario_id
    );
END;
$$;
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::unprepared('DROP PROCEDURE IF EXISTS sp_emitir_licencia_funcionamiento;');
    }
};
