with open('database/migrations/2026_01_01_000001_create_test_tables.php', 'r') as f:
    c = f.read()

import re
c = re.sub(r"Schema::create\('tipos_documento_identidad', function \(Blueprint \$table\) \{.*?\$table->string\('codigo'\);\s*\$table->string\('descripcion'\);\s*\}\);", r"""Schema::create('tipos_documento_identidad', function (Blueprint $table) {
                    $table->id();
                    $table->string('codigo', 20)->unique();
                    $table->string('nombre', 100);
                    $table->boolean('esta_activo')->default(true);
                    $table->boolean('es_para_natural')->default(true);
                    $table->boolean('es_para_juridica')->default(false);
                });""", c, flags=re.DOTALL)

with open('database/migrations/2026_01_01_000001_create_test_tables.php', 'w') as f:
    f.write(c)

