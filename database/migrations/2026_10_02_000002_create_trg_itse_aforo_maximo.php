<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Corte 2 (equipo): bloqueo de aforo en ITSE según CNE / RNE (2 personas por m² de área útil).
 *
 * Ajustes al integrarlo en main:
 * - En UPDATE solo se valida si cambian el área o el aforo: así no se bloquean cambios
 *   ajenos (p. ej. registrar la notificación) en certificados antiguos que no cumplan la regla.
 * - Código de error propio (LF001) para reconocerlo en el backend. La aplicación ya valida
 *   la misma regla con un mensaje claro (Itse::AFORO_POR_M2); el trigger es la última defensa.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::unprepared(<<<'SQL'
CREATE OR REPLACE FUNCTION fn_trg_itse_aforo_maximo()
RETURNS TRIGGER AS $$
DECLARE
    v_aforo_maximo INTEGER;
BEGIN
    IF TG_OP = 'UPDATE'
       AND NEW.area IS NOT DISTINCT FROM OLD.area
       AND NEW.capacidad_aforo IS NOT DISTINCT FROM OLD.capacidad_aforo THEN
        RETURN NEW;
    END IF;

    -- Validación de aforo máximo según normativa CNE / RNE: 2 personas por m2 de área útil
    IF NEW.area IS NOT NULL AND NEW.area > 0 THEN
        v_aforo_maximo := FLOOR(NEW.area * 2)::INTEGER;
        IF NEW.capacidad_aforo > v_aforo_maximo THEN
            RAISE EXCEPTION 'Aforo excedido: el aforo máximo permitido es de % personas para un área de % m2', v_aforo_maximo, NEW.area
                USING ERRCODE = 'LF001';
        END IF;
    END IF;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_itse_aforo_maximo ON itse;

CREATE TRIGGER trg_itse_aforo_maximo
BEFORE INSERT OR UPDATE ON itse
FOR EACH ROW
EXECUTE FUNCTION fn_trg_itse_aforo_maximo();
SQL
        );
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::unprepared(<<<'SQL'
DROP TRIGGER IF EXISTS trg_itse_aforo_maximo ON itse;
DROP FUNCTION IF EXISTS fn_trg_itse_aforo_maximo();
SQL
        );
    }
};
