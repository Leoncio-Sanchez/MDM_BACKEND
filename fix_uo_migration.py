with open('database/migrations/2026_01_01_000001_create_test_tables.php', 'r') as f:
    c = f.read()

import re

uo_migration = """
                Schema::create('unidades_organicas', function (Blueprint $table) {
                    $table->id();
                    $table->string('codigo', 20)->unique();
                    $table->string('nombre', 100);
                    $table->boolean('esta_activo')->default(true);
                });
"""

c = re.sub(r"Schema::create\('tipos_procedimiento_tupa', function \(Blueprint \$table\) \{", uo_migration + r"\n                Schema::create('tipos_procedimiento_tupa', function (Blueprint $table) {", c)

with open('database/migrations/2026_01_01_000001_create_test_tables.php', 'w') as f:
    f.write(c)
