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
CREATE OR REPLACE FUNCTION fn_trg_itse_aforo_maximo()
RETURNS TRIGGER AS $$
DECLARE
    v_aforo_maximo INTEGER;
BEGIN
    -- Validación de aforo máximo según normativa CNE / RNE: 2 personas por m2 de área útil
    IF NEW.area IS NOT NULL AND NEW.area > 0 THEN
        v_aforo_maximo := FLOOR(NEW.area * 2)::INTEGER;
        IF NEW.capacidad_aforo > v_aforo_maximo THEN
            RAISE EXCEPTION 'Aforo excedido: el aforo máximo permitido es de % personas para un área de % m2', v_aforo_maximo, NEW.area;
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

    /**
     * Reverse the migrations.
     */
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
