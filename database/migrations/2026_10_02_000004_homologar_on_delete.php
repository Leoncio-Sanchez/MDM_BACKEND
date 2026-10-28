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
-- 1. expedientes_archivos -> ON DELETE CASCADE
ALTER TABLE expedientes_archivos
    DROP CONSTRAINT IF EXISTS expedientes_archivos_expediente_id_22c81044_fk_expedientes_id,
    ADD CONSTRAINT expedientes_archivos_expediente_id_fk
        FOREIGN KEY (expediente_id) REFERENCES expedientes(id) ON DELETE CASCADE;

-- 2. licencias_funcionamiento_archivos -> ON DELETE CASCADE
ALTER TABLE licencias_funcionamiento_archivos
    DROP CONSTRAINT IF EXISTS licencias_funcionami_licencia_funcionamie_27cabff7_fk_licencias,
    ADD CONSTRAINT licencias_funcionamiento_archivos_licencia_id_fk
        FOREIGN KEY (licencia_funcionamiento_id) REFERENCES licencias_funcionamiento(id) ON DELETE CASCADE;

-- 3. licencias_funcionamiento_estados -> ON DELETE CASCADE
ALTER TABLE licencias_funcionamiento_estados
    DROP CONSTRAINT IF EXISTS licencias_funcionami_licencia_funcionamie_95591e9c_fk_licencias,
    ADD CONSTRAINT licencias_funcionamiento_estados_licencia_id_fk
        FOREIGN KEY (licencia_funcionamiento_id) REFERENCES licencias_funcionamiento(id) ON DELETE CASCADE;

-- 4. licencias_funcionamiento_giros -> ON DELETE CASCADE
ALTER TABLE licencias_funcionamiento_giros
    DROP CONSTRAINT IF EXISTS licencias_funcionami_licencia_funcionamie_aa355771_fk_licencias,
    ADD CONSTRAINT licencias_funcionamiento_giros_licencia_id_fk
        FOREIGN KEY (licencia_funcionamiento_id) REFERENCES licencias_funcionamiento(id) ON DELETE CASCADE;

-- 5. itse_archivos -> ON DELETE CASCADE
ALTER TABLE itse_archivos
    DROP CONSTRAINT IF EXISTS itse_archivos_itse_id_3bd7d6a9_fk_itse_id,
    ADD CONSTRAINT itse_archivos_itse_id_fk
        FOREIGN KEY (itse_id) REFERENCES itse(id) ON DELETE CASCADE;

-- 6. itse_estados -> ON DELETE CASCADE
ALTER TABLE itse_estados
    DROP CONSTRAINT IF EXISTS itse_estados_itse_id_219803f9_fk_itse_id,
    ADD CONSTRAINT itse_estados_itse_id_fk
        FOREIGN KEY (itse_id) REFERENCES itse(id) ON DELETE CASCADE;

-- 7. itse_giros -> ON DELETE CASCADE
ALTER TABLE itse_giros
    DROP CONSTRAINT IF EXISTS itse_giros_itse_id_3fee7732_fk_itse_id,
    ADD CONSTRAINT itse_giros_itse_id_fk
        FOREIGN KEY (itse_id) REFERENCES itse(id) ON DELETE CASCADE;

-- 8. personas_documentos -> ON DELETE CASCADE
ALTER TABLE personas_documentos
    DROP CONSTRAINT IF EXISTS personas_documentos_persona_id_74a7c7f1_fk_personas_id,
    ADD CONSTRAINT personas_documentos_persona_id_fk
        FOREIGN KEY (persona_id) REFERENCES personas(id) ON DELETE CASCADE;

-- 9. licencias_funcionamiento.conductor_id -> ON DELETE SET NULL
ALTER TABLE licencias_funcionamiento
    DROP CONSTRAINT IF EXISTS licencias_funcionamiento_conductor_id_cf87f9e7_fk_personas_id,
    ADD CONSTRAINT licencias_funcionamiento_conductor_id_fk
        FOREIGN KEY (conductor_id) REFERENCES personas(id) ON DELETE SET NULL;

-- 10. expedientes.representante_id -> ON DELETE SET NULL
ALTER TABLE expedientes
    DROP CONSTRAINT IF EXISTS expedientes_representante_id_b06a7961_fk_personas_id,
    ADD CONSTRAINT expedientes_representante_id_fk
        FOREIGN KEY (representante_id) REFERENCES personas(id) ON DELETE SET NULL;
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
ALTER TABLE expedientes_archivos
    DROP CONSTRAINT IF EXISTS expedientes_archivos_expediente_id_fk,
    ADD CONSTRAINT expedientes_archivos_expediente_id_22c81044_fk_expedientes_id
        FOREIGN KEY (expediente_id) REFERENCES expedientes(id) DEFERRABLE INITIALLY DEFERRED;

ALTER TABLE licencias_funcionamiento_archivos
    DROP CONSTRAINT IF EXISTS licencias_funcionamiento_archivos_licencia_id_fk,
    ADD CONSTRAINT licencias_funcionami_licencia_funcionamie_27cabff7_fk_licencias
        FOREIGN KEY (licencia_funcionamiento_id) REFERENCES licencias_funcionamiento(id) DEFERRABLE INITIALLY DEFERRED;

ALTER TABLE licencias_funcionamiento_estados
    DROP CONSTRAINT IF EXISTS licencias_funcionamiento_estados_licencia_id_fk,
    ADD CONSTRAINT licencias_funcionami_licencia_funcionamie_95591e9c_fk_licencias
        FOREIGN KEY (licencia_funcionamiento_id) REFERENCES licencias_funcionamiento(id) DEFERRABLE INITIALLY DEFERRED;

ALTER TABLE licencias_funcionamiento_giros
    DROP CONSTRAINT IF EXISTS licencias_funcionamiento_giros_licencia_id_fk,
    ADD CONSTRAINT licencias_funcionami_licencia_funcionamie_aa355771_fk_licencias
        FOREIGN KEY (licencia_funcionamiento_id) REFERENCES licencias_funcionamiento(id) DEFERRABLE INITIALLY DEFERRED;

ALTER TABLE itse_archivos
    DROP CONSTRAINT IF EXISTS itse_archivos_itse_id_fk,
    ADD CONSTRAINT itse_archivos_itse_id_3bd7d6a9_fk_itse_id
        FOREIGN KEY (itse_id) REFERENCES itse(id) DEFERRABLE INITIALLY DEFERRED;

ALTER TABLE itse_estados
    DROP CONSTRAINT IF EXISTS itse_estados_itse_id_fk,
    ADD CONSTRAINT itse_estados_itse_id_219803f9_fk_itse_id
        FOREIGN KEY (itse_id) REFERENCES itse(id) DEFERRABLE INITIALLY DEFERRED;

ALTER TABLE itse_giros
    DROP CONSTRAINT IF EXISTS itse_giros_itse_id_fk,
    ADD CONSTRAINT itse_giros_itse_id_3fee7732_fk_itse_id
        FOREIGN KEY (itse_id) REFERENCES itse(id) DEFERRABLE INITIALLY DEFERRED;

ALTER TABLE personas_documentos
    DROP CONSTRAINT IF EXISTS personas_documentos_persona_id_fk,
    ADD CONSTRAINT personas_documentos_persona_id_74a7c7f1_fk_personas_id
        FOREIGN KEY (persona_id) REFERENCES personas(id) DEFERRABLE INITIALLY DEFERRED;

ALTER TABLE licencias_funcionamiento
    DROP CONSTRAINT IF EXISTS licencias_funcionamiento_conductor_id_fk,
    ADD CONSTRAINT licencias_funcionamiento_conductor_id_cf87f9e7_fk_personas_id
        FOREIGN KEY (conductor_id) REFERENCES personas(id) DEFERRABLE INITIALLY DEFERRED;

ALTER TABLE expedientes
    DROP CONSTRAINT IF EXISTS expedientes_representante_id_fk,
    ADD CONSTRAINT expedientes_representante_id_b06a7961_fk_personas_id
        FOREIGN KEY (representante_id) REFERENCES personas(id) DEFERRABLE INITIALLY DEFERRED;
SQL
        );
    }
};
