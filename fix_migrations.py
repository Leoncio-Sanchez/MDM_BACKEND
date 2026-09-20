import os

with open('database/migrations/2026_01_01_000001_create_test_tables.php', 'r') as f:
    c = f.read()

# Remove the previously injected tables
c = c.replace("""    Schema::create('personas', function (Blueprint $table) {
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
        $table->timestamp('fecha_creacion');
        $table->timestamp('fecha_actualizacion');
        $table->foreignId('user_id');
    });

    Schema::create('personas_documentos', function (Blueprint $table) {
        $table->id();
        $table->foreignId('persona_id');
        $table->foreignId('tipo_documento_identidad_id');
        $table->string('numero_documento', 20);
        $table->unique(['persona_id', 'tipo_documento_identidad_id']);
    });

    Schema::create('expedientes', function (Blueprint $table) {
        $table->id();
        $table->foreignId('solicitante_id');
        $table->foreignId('representante_id');
    });

    Schema::create('licencias_funcionamiento', function (Blueprint $table) {
        $table->id();
        $table->foreignId('titular_id');
        $table->foreignId('conductor_id');
    });

    Schema::create('itse', function (Blueprint $table) {
        $table->id();
        $table->foreignId('titular_id');
        $table->foreignId('conductor_id');
    });
""", "")

with open('database/migrations/2026_01_01_000001_create_test_tables.php', 'w') as f:
    f.write(c)

