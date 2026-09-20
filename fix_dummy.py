import re

with open('database/migrations/2026_01_01_000001_create_test_tables.php', 'r') as f:
    c = f.read()

pattern = r"if \(\!Schema::hasTable\('personas'\)\) \{\n\s*Schema::create\('personas', function \(Blueprint \$table\) \{.*?\n\s*\}\);\n\s*\}"
new_personas = """if (!Schema::hasTable('personas')) {
                Schema::create('personas', function (Blueprint $table) {
                    $table->id();
                    $table->string('tipo_persona', 1)->default('N');
                    $table->string('sexo', 1)->default('X');
                    $table->string('apellido_paterno', 50)->nullable();
                    $table->string('apellido_materno', 50)->nullable();
                    $table->string('nombres', 100);
                    $table->string('direccion', 250)->nullable();
                    $table->string('distrito', 100)->nullable();
                    $table->string('provincia', 100)->nullable();
                    $table->string('departamento', 100)->nullable();
                    $table->string('telefono', 30)->nullable();
                    $table->string('correo_electronico', 150)->nullable();
                    $table->timestamp('fecha_creacion')->nullable();
                    $table->timestamp('fecha_actualizacion')->nullable();
                    $table->foreignId('user_id')->default(1);
                });
            }"""

c = re.sub(pattern, new_personas, c, flags=re.DOTALL)

with open('database/migrations/2026_01_01_000001_create_test_tables.php', 'w') as f:
    f.write(c)

