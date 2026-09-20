with open('database/migrations/2026_01_01_000001_create_test_tables.php', 'r') as f:
    c = f.read()

import re

giros_migration = """
                Schema::create('giros', function (Blueprint $table) {
                    $table->id();
                    $table->integer('ciiu_id')->nullable()->unique();
                    $table->string('nombre', 200);
                    $table->boolean('esta_activo')->default(true);
                    $table->timestamp('fecha_digitacion')->useCurrent();
                    $table->foreignId('usuario_id')->constrained('auth_user');
                });
"""

c = re.sub(r"Schema::create\('licencias_funcionamiento_giros', function \(Blueprint \$table\) \{", giros_migration + r"\n                Schema::create('licencias_funcionamiento_giros', function (Blueprint $table) {", c)

with open('database/migrations/2026_01_01_000001_create_test_tables.php', 'w') as f:
    f.write(c)
