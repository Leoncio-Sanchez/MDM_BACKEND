import os

with open('database/migrations/2026_01_01_000001_create_test_tables.php', 'r') as f:
    c = f.read()

injection = """
        if (!Schema::hasTable('personas')) {
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
        }
        if (!Schema::hasTable('personas_documentos')) {
            Schema::create('personas_documentos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('persona_id');
                $table->foreignId('tipo_documento_identidad_id');
                $table->string('numero_documento', 20);
            });
        }
        if (!Schema::hasTable('expedientes')) {
            Schema::create('expedientes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('solicitante_id');
                $table->foreignId('representante_id');
            });
        }
"""
c = c.replace("Schema::create('auth_user', function (Blueprint $table) {", injection + "\n        Schema::create('auth_user', function (Blueprint $table) {")

with open('database/migrations/2026_01_01_000001_create_test_tables.php', 'w') as f:
    f.write(c)

