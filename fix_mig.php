<?php
$file = 'database/migrations/2026_01_01_000001_create_test_tables.php';
$content = file_get_contents($file);
$content = str_replace(
"            if (!Schema::hasTable('niveles_riesgo')) {
                Schema::create('niveles_riesgo', function (Blueprint \$table) {
                    \$table->id();
                    \$table->string('nombre');
                });
            }",
"            if (!Schema::hasTable('niveles_riesgo')) {
                Schema::create('niveles_riesgo', function (Blueprint \$table) {
                    \$table->id();
                    \$table->string('codigo', 10)->unique();
                    \$table->string('nombre', 50);
                    \$table->boolean('esta_activo')->default(true);
                });
            }", $content);
file_put_contents($file, $content);
