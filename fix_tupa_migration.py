with open('database/migrations/2026_01_01_000001_create_test_tables.php', 'r') as f:
    c = f.read()

import re

tupa_migration = """
                Schema::create('tipos_procedimiento_tupa', function (Blueprint $table) {
                    $table->id();
                    $table->string('codigo', 30)->unique();
                    $table->string('nombre', 200);
                    $table->integer('plazo_atencion_dias');
                    $table->integer('dias_alerta_vencimiento');
                    $table->boolean('esta_activo')->default(true);
                    $table->boolean('requiere_lf')->default(false);
                    $table->boolean('requiere_itse')->default(false);
                    $table->timestamp('fecha_digitacion');
                    $table->foreignId('usuario_id')->constrained('auth_user');
                    $table->foreignId('unidad_organica_id')->constrained('unidades_organicas');
                    $table->decimal('monto', 18, 2)->nullable();
                });
"""

c = re.sub(r"Schema::create\('licencias_funcionamiento_giros', function \(Blueprint \$table\) \{", tupa_migration + r"\n                Schema::create('licencias_funcionamiento_giros', function (Blueprint $table) {", c)

with open('database/migrations/2026_01_01_000001_create_test_tables.php', 'w') as f:
    f.write(c)
