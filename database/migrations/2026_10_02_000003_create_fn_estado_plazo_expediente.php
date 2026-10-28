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
-- 1. Función auxiliar para verificar si una fecha dada es día hábil
CREATE OR REPLACE FUNCTION fn_es_dia_habil(p_fecha DATE)
RETURNS BOOLEAN AS $$
BEGIN
    -- Excluir sábados (6) y domingos (0)
    IF EXTRACT(DOW FROM p_fecha) IN (0, 6) THEN
        RETURN FALSE;
    END IF;

    -- Excluir feriados recurrentes (mismo día y mes todos los años)
    IF EXISTS (
        SELECT 1 FROM feriados_recurrentes
        WHERE dia = EXTRACT(DAY FROM p_fecha)
          AND mes = EXTRACT(MONTH FROM p_fecha)
    ) THEN
        RETURN FALSE;
    END IF;

    -- Excluir feriados anuales específicos
    IF EXISTS (
        SELECT 1 FROM feriados_anuales
        WHERE feriado::date = p_fecha
    ) THEN
        RETURN FALSE;
    END IF;

    RETURN TRUE;
END;
$$ LANGUAGE plpgsql STABLE;

-- 2. Función auxiliar para avanzar días hábiles
CREATE OR REPLACE FUNCTION fn_avanzar_dias_habiles(p_fecha_inicio DATE, p_dias INTEGER)
RETURNS DATE AS $$
DECLARE
    v_fecha DATE := p_fecha_inicio;
    v_contador INTEGER := 0;
BEGIN
    IF p_dias <= 0 THEN
        RETURN v_fecha;
    END IF;

    WHILE v_contador < p_dias LOOP
        v_fecha := v_fecha + INTERVAL '1 day';
        IF fn_es_dia_habil(v_fecha) THEN
            v_contador := v_contador + 1;
        END IF;
    END LOOP;

    RETURN v_fecha;
END;
$$ LANGUAGE plpgsql STABLE;

-- 3. Función auxiliar para retroceder días hábiles (cálculo de fecha_alerta)
CREATE OR REPLACE FUNCTION fn_retroceder_dias_habiles(p_fecha_vencimiento DATE, p_dias INTEGER)
RETURNS DATE AS $$
DECLARE
    v_fecha DATE := p_fecha_vencimiento;
    v_contador INTEGER := 0;
BEGIN
    IF p_dias <= 0 THEN
        RETURN v_fecha;
    END IF;

    WHILE v_contador < p_dias LOOP
        v_fecha := v_fecha - INTERVAL '1 day';
        IF fn_es_dia_habil(v_fecha) THEN
            v_contador := v_contador + 1;
        END IF;
    END LOOP;

    RETURN v_fecha;
END;
$$ LANGUAGE plpgsql STABLE;

-- 4. Función auxiliar para calcular diferencia de días hábiles entre dos fechas
CREATE OR REPLACE FUNCTION fn_diferencia_dias_habiles(p_desde DATE, p_hasta DATE)
RETURNS INTEGER AS $$
DECLARE
    v_cursor DATE;
    v_menor DATE;
    v_mayor DATE;
    v_avanzando BOOLEAN;
    v_conteo INTEGER := 0;
BEGIN
    IF p_desde = p_hasta THEN
        RETURN 0;
    END IF;

    v_avanzando := (p_hasta > p_desde);
    IF v_avanzando THEN
        v_menor := p_desde;
        v_mayor := p_hasta;
    ELSE
        v_menor := p_hasta;
        v_mayor := p_desde;
    END IF;

    v_cursor := v_menor;
    WHILE v_cursor < v_mayor LOOP
        v_cursor := v_cursor + INTERVAL '1 day';
        IF fn_es_dia_habil(v_cursor) THEN
            v_conteo := v_conteo + 1;
        END IF;
    END LOOP;

    IF v_avanzando THEN
        RETURN v_conteo;
    ELSE
        RETURN -v_conteo;
    END IF;
END;
$$ LANGUAGE plpgsql STABLE;

-- 5. Función principal que calcula la fecha de vencimiento e itera días hábiles
CREATE OR REPLACE FUNCTION fn_estado_plazo_expediente(p_expediente_id BIGINT)
RETURNS TABLE (estado VARCHAR(20), dias_restantes INTEGER) AS $$
DECLARE
    v_fecha_recepcion DATE;
    v_plazo_dias INTEGER;
    v_dias_alerta INTEGER;
    v_dias_ampliacion INTEGER;
    v_vencimiento_calc DATE;
    v_alerta_calc DATE;
    v_hoy DATE := CURRENT_DATE;
    v_diff INTEGER;
BEGIN
    -- Obtener fecha de recepción y plazo del TUPA sin leer fechas precalculadas
    SELECT 
        e.fecha_recepcion::DATE,
        COALESCE(tpt.plazo_atencion_dias, 0),
        COALESCE(tpt.dias_alerta_vencimiento, 0),
        COALESCE(e.dias_ampliacion, 0)
    INTO 
        v_fecha_recepcion,
        v_plazo_dias,
        v_dias_alerta,
        v_dias_ampliacion
    FROM expedientes e
    JOIN tipos_procedimiento_tupa tpt ON e.tipo_procedimiento_tupa_id = tpt.id
    WHERE e.id = p_expediente_id;

    IF NOT FOUND THEN
        RETURN QUERY SELECT 'NO_EXISTE'::VARCHAR(20), 0::INTEGER;
        RETURN;
    END IF;

    -- Calcular fecha de vencimiento iterando días hábiles
    v_vencimiento_calc := fn_avanzar_dias_habiles(v_fecha_recepcion, v_plazo_dias);

    -- Si hubo ampliación de plazo, sumar días adicionales
    IF v_dias_ampliacion > 0 THEN
        v_vencimiento_calc := fn_avanzar_dias_habiles(v_vencimiento_calc, v_dias_ampliacion);
    END IF;

    -- Calcular fecha de alerta retrocediendo días hábiles
    v_alerta_calc := fn_retroceder_dias_habiles(v_vencimiento_calc, v_dias_alerta);

    -- Calcular días restantes (hoy hasta vencimiento)
    v_diff := fn_diferencia_dias_habiles(v_hoy, v_vencimiento_calc);

    -- Determinar estado dinámico calculado en tiempo de ejecución
    IF v_hoy > v_vencimiento_calc THEN
        RETURN QUERY SELECT 'VENCIDO'::VARCHAR(20), v_diff;
    ELSIF v_hoy >= v_alerta_calc THEN
        RETURN QUERY SELECT 'EN_ALERTA'::VARCHAR(20), v_diff;
    ELSE
        RETURN QUERY SELECT 'EN_PLAZO'::VARCHAR(20), v_diff;
    END IF;
END;
$$ LANGUAGE plpgsql STABLE;
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

        DB::unprepared(<<<'SQL'
DROP FUNCTION IF EXISTS fn_estado_plazo_expediente(BIGINT);
DROP FUNCTION IF EXISTS fn_diferencia_dias_habiles(DATE, DATE);
DROP FUNCTION IF EXISTS fn_retroceder_dias_habiles(DATE, INTEGER);
DROP FUNCTION IF EXISTS fn_avanzar_dias_habiles(DATE, INTEGER);
DROP FUNCTION IF EXISTS fn_es_dia_habil(DATE);
SQL
        );
    }
};
