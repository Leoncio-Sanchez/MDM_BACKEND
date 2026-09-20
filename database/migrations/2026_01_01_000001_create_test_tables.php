<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (app()->environment('testing')) {
            if (!Schema::hasTable('tipos_licencia')) {
                Schema::create('tipos_licencia', function (Blueprint $table) {
                    $table->id();
                    $table->string('nombre');
                    $table->boolean('esta_activo')->default(true);
                });
            }

            if (!Schema::hasTable('expedientes')) {
                Schema::create('expedientes', function (Blueprint $table) {
                    $table->id();
                    $table->integer('numero');
                    $table->date('fecha_ingreso');
                    $table->foreignId('tipo_procedimiento_tupa_id')->nullable();
                });
            }

            if (!Schema::hasTable('personas')) {
                Schema::create('personas', function (Blueprint $table) {
                    $table->id();
                    $table->string('nombres');
                    $table->string('apellido_paterno');
                    $table->string('apellido_materno');
                    $table->string('direccion')->nullable();
                    $table->string('distrito')->nullable();
                    $table->string('provincia')->nullable();
                    $table->string('departamento')->nullable();
                    $table->string('telefono')->nullable();
                    $table->string('correo_electronico')->nullable();
                });
            }

            if (!Schema::hasTable('niveles_riesgo')) {
                Schema::create('niveles_riesgo', function (Blueprint $table) {
                    $table->id();
$table->string('codigo', 10)->unique();
                    $table->string('nombre', 50);
                    $table->boolean('esta_activo')->default(true);
                });
            }

            if (!Schema::hasTable('zonificaciones')) {
                Schema::create('zonificaciones', function (Blueprint $table) {
                    $table->id();
                    $table->string('codigo', 30)->unique();
                    $table->string('nombre', 150);
                    $table->boolean('esta_activo')->default(true);
                    $table->timestampTz('fecha_digitacion')->useCurrent();
                    $table->unsignedBigInteger('usuario_id')->default(1);
                });
            }

            if (!Schema::hasTable('licencias_funcionamiento')) {
                Schema::create('licencias_funcionamiento', function (Blueprint $table) {
                    $table->id();
                    $table->uuid('uuid')->unique();
                    $table->integer('numero_licencia')->unique();
                    $table->date('fecha_emision');
                    $table->string('nombre_comercial', 250);
                    $table->foreignId('titular_id');
                    $table->foreignId('conductor_id');
                    $table->foreignId('licencia_principal_id')->nullable();
                    $table->foreignId('expediente_id');
                    $table->foreignId('tipo_licencia_id');
                    $table->foreignId('nivel_riesgo_id');
                    $table->foreignId('zonificacion_id');
                    $table->boolean('es_vigencia_indeterminada')->default(true);
                    $table->date('fecha_inicio_vigencia')->nullable();
                    $table->date('fecha_fin_vigencia')->nullable();
                    $table->string('actividad', 50);
                    $table->string('direccion', 250);
                    $table->integer('hora_desde');
                    $table->integer('hora_hasta');
                    $table->string('resolucion_numero', 50);
                    $table->decimal('area', 18, 2);
                    $table->string('numero_recibo_pago', 20);
                    $table->text('observaciones')->nullable();
                    $table->boolean('se_puede_publicar')->default(false);
                    $table->timestamp('fecha_notificacion')->nullable();
                    $table->string('estado', 20);
                });
            }

            if (!Schema::hasTable('licencias_funcionamiento_estados')) {
                Schema::create('licencias_funcionamiento_estados', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('licencia_funcionamiento_id');
                    $table->foreignId('estado_id');
                    $table->date('fecha_estado');
                    $table->string('documento', 100)->nullable();
                    $table->string('observaciones', 1000)->nullable();
                    $table->foreignId('usuario_id')->nullable();
                    $table->timestamp('fecha_digitacion')->nullable();
                });
            }

            if (!Schema::hasTable('licencias_funcionamiento_archivos')) {
                Schema::create('licencias_funcionamiento_archivos', function (Blueprint $table) {
                    $table->id();
                    $table->uuid('uuid');
                    $table->foreignId('licencia_funcionamiento_id');
                    $table->string('nombre_original', 255);
                    $table->string('nombre_archivo', 255);
                    $table->string('ruta_archivo', 500);
                });
            }

            if (!Schema::hasTable('licencias_funcionamiento_giros')) {
                Schema::create('licencias_funcionamiento_giros', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('licencia_funcionamiento_id');
                    $table->foreignId('giro_id');
                    $table->foreignId('usuario_id')->nullable();
                    $table->timestamp('fecha_digitacion')->nullable();
                });
            }

            if (!Schema::hasTable('autorizaciones_improcedentes')) {
                Schema::create('autorizaciones_improcedentes', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('expediente_id');
                    $table->string('tipo_autorizacion', 10);
                    $table->boolean('esta_activo')->default(true);
                });
            }

            if (!Schema::hasTable('auth_user')) {
                Schema::create('auth_user', function (Blueprint $table) {
                    $table->id();
                    $table->string('username')->unique();
                    $table->string('email')->nullable();
                    $table->string('password');
                    $table->string('role')->nullable();
                    $table->string('ruc')->nullable();
                    $table->boolean('is_active')->default(true);
                    $table->boolean('is_staff')->default(false);
                    $table->boolean('is_superuser')->default(false);
                    $table->timestamps();
                });
            }
            
            if (!Schema::hasTable('estados')) {
                Schema::create('estados', function (Blueprint $table) {
                    $table->id();
                    $table->string('nombre');
                    $table->boolean('es_para_lf')->default(true);
                    $table->boolean('es_para_itse')->default(true);
                    $table->boolean('esta_activo')->default(true);
                });
            }
        }
    }
    public function down(): void {}
};
