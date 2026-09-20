import re

with open('database/migrations/2026_01_01_000001_create_test_tables.php', 'r') as f:
    c = f.read()

# Replace the first `personas` definition
original_personas = """            if (!Schema::hasTable('personas')) {
                Schema::create('personas', function (Blueprint $table) {
                    $table->id();
                    $table->string('nombres');
                    $table->string('apellido_paterno');
                    $table->string('apellido_materno');
                    $table->string('direccion')->nullable();
                    $table->string('distrito')->nullable();
                    $table->string('provincia')->nullable();
                    $table->string('departamento')->nullable();
                });
            }"""

new_personas = """            if (!Schema::hasTable('personas')) {
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

if original_personas in c:
    c = c.replace(original_personas, new_personas)
else:
    print("WARNING: Could not find original_personas to replace. Maybe format is different.")

# Remove the duplicated injection I made
duplicated = """        if (!Schema::hasTable('personas')) {
            Schema::create('personas', function (Blueprint $table) {
                $table->id();
                $table->string('tipo_persona', 1);
                $table->string('sexo', 1)->default('X');
                $table->string('apellido_paterno', 50)->nullable();
                $table->string('apellido_materno', 50)->nullable();
                $table->string('nombres', 100);
                $table->string('direccion', 250);
                $table->string('distrito', 100);
                $table->string('provincia', 100);
                $table->string('departamento', 100);
                $table->string('telefono', 30)->nullable();
                $table->string('correo_electronico', 150)->nullable();
                $table->timestamp('fecha_creacion')->nullable();
                $table->timestamp('fecha_actualizacion')->nullable();
                $table->foreignId('user_id');
            });
        }"""
c = c.replace(duplicated, "")

with open('database/migrations/2026_01_01_000001_create_test_tables.php', 'w') as f:
    f.write(c)

