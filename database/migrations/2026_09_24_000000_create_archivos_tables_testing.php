<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (app()->environment(['testing', 'local'])) {
            if (! Schema::hasTable('expedientes_archivos')) {
                Schema::create('expedientes_archivos', function (Blueprint $table) {
                    $table->id();
                    $table->uuid('uuid')->unique();
                    $table->string('nombre_original', 255);
                    $table->string('nombre_archivo', 255);
                    $table->string('ruta_archivo', 500);
                    $table->string('extension', 20);
                    $table->bigInteger('tamanio_bytes');
                    $table->timestamp('fecha_digitacion')->nullable();
                    $table->foreignId('expediente_id');
                    $table->foreignId('usuario_id');
                });
            }

            if (! Schema::hasTable('itse_archivos')) {
                Schema::create('itse_archivos', function (Blueprint $table) {
                    $table->id();
                    $table->uuid('uuid')->unique();
                    $table->string('nombre_original', 255);
                    $table->string('nombre_archivo', 255);
                    $table->string('ruta_archivo', 500);
                    $table->string('extension', 20);
                    $table->bigInteger('tamanio_bytes');
                    $table->timestamp('fecha_digitacion')->nullable();
                    $table->foreignId('itse_id');
                    $table->foreignId('usuario_id');
                });
            }

            if (! Schema::hasTable('licencias_funcionamiento_archivos')) {
                Schema::create('licencias_funcionamiento_archivos', function (Blueprint $table) {
                    $table->id();
                    $table->uuid('uuid')->unique();
                    $table->string('nombre_original', 255);
                    $table->string('nombre_archivo', 255);
                    $table->string('ruta_archivo', 500);
                    $table->string('extension', 20);
                    $table->bigInteger('tamanio_bytes');
                    $table->timestamp('fecha_digitacion')->nullable();
                    $table->foreignId('licencia_funcionamiento_id');
                    $table->foreignId('usuario_id');
                });
            }
        }
    }

    public function down(): void
    {
        if (app()->environment('testing')) {
            Schema::dropIfExists('licencias_funcionamiento_archivos');
            Schema::dropIfExists('itse_archivos');
            Schema::dropIfExists('expedientes_archivos');
        }
    }
};
