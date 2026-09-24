<?php

declare(strict_types=1);
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (app()->environment('testing')) {
            if (! Schema::hasTable('tipos_licencia')) {
                Schema::create('tipos_licencia', function (Blueprint $table) {
                    $table->id();
                    $table->string('nombre');
                    $table->boolean('esta_activo')->default(true);
                });
            }

            if (! Schema::hasTable('expedientes')) {
                Schema::create('expedientes', function (Blueprint $table) {
                    $table->id();
                    $table->uuid('uuid')->nullable();
                    $table->integer('numero')->nullable();
                    $table->integer('numero_expediente')->nullable();
                    $table->dateTime('fecha_recepcion')->nullable();
                    $table->date('fecha_ingreso')->nullable();
                    $table->foreignId('tipo_procedimiento_tupa_id')->nullable();
                    $table->foreignId('solicitante_id')->nullable();
                    $table->foreignId('representante_id')->nullable();
                    $table->string('observaciones', 250)->nullable();
                    $table->date('fecha_vencimiento')->nullable();
                    $table->date('fecha_alerta')->nullable();
                    $table->date('fecha_suspension')->nullable();
                    $table->integer('dias_ampliacion')->nullable();
                    $table->string('motivo_ampliacion', 250)->nullable();
                    $table->foreignId('usuario_ampliacion')->nullable();
                    $table->dateTime('fecha_digitacion_ampliacion')->nullable();
                    $table->foreignId('usuario_id')->nullable();
                    $table->dateTime('fecha_digitacion')->nullable();
                });
            }

            if (! Schema::hasTable('feriados_recurrentes')) {
                Schema::create('feriados_recurrentes', function (Blueprint $table) {
                    $table->id();
                    $table->smallInteger('dia');
                    $table->smallInteger('mes');
                    $table->unique(['dia', 'mes']);
                });
            }

            if (! Schema::hasTable('feriados_anuales')) {
                Schema::create('feriados_anuales', function (Blueprint $table) {
                    $table->id();
                    $table->date('feriado')->unique();
                    $table->timestamp('fecha_digitacion')->nullable();
                    $table->foreignId('user_id')->nullable();
                });
            }

            if (! Schema::hasTable('personas')) {
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
            }

            if (! Schema::hasTable('niveles_riesgo')) {
                Schema::create('niveles_riesgo', function (Blueprint $table) {
                    $table->id();
                    $table->string('codigo', 10)->unique();
                    $table->string('nombre', 50);
                    $table->boolean('esta_activo')->default(true);
                });
            }

            if (! Schema::hasTable('zonificaciones')) {
                Schema::create('zonificaciones', function (Blueprint $table) {
                    $table->id();
                    $table->string('codigo', 30)->unique();
                    $table->string('nombre', 150);
                    $table->boolean('esta_activo')->default(true);
                    $table->timestampTz('fecha_digitacion')->useCurrent();
                    $table->unsignedBigInteger('usuario_id')->default(1);
                });
            }

            if (! Schema::hasTable('licencias_funcionamiento')) {
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

            if (! Schema::hasTable('licencias_funcionamiento_estados')) {
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

            if (! Schema::hasTable('licencias_funcionamiento_giros')) {

                Schema::create('giros', function (Blueprint $table) {
                    $table->id();
                    $table->integer('ciiu_id')->nullable()->unique();
                    $table->string('nombre', 200);
                    $table->boolean('esta_activo')->default(true);
                    $table->timestamp('fecha_digitacion')->useCurrent();
                    $table->foreignId('usuario_id')->constrained('auth_user');
                });

                Schema::create('unidades_organicas', function (Blueprint $table) {
                    $table->id();
                    $table->string('codigo', 20)->unique();
                    $table->string('nombre', 100);
                    $table->boolean('esta_activo')->default(true);
                });

                Schema::create('tipos_procedimiento_tupa', function (Blueprint $table) {
                    $table->id();
                    $table->string('codigo', 30)->unique();
                    $table->string('nombre', 200);
                    $table->integer('plazo_atencion_dias');
                    $table->integer('dias_alerta_vencimiento');
                    $table->boolean('esta_activo')->default(true);
                    $table->boolean('requiere_lf')->default(false);
                    $table->boolean('requiere_itse')->default(false);
                    $table->timestamp('fecha_digitacion');
                    $table->foreignId('usuario_id')->constrained('auth_user');
                    $table->foreignId('unidad_organica_id')->constrained('unidades_organicas');
                    $table->decimal('monto', 18, 2)->nullable();
                });

                Schema::create('licencias_funcionamiento_giros', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('licencia_funcionamiento_id');
                    $table->foreignId('giro_id');
                    $table->foreignId('usuario_id')->nullable();
                    $table->timestamp('fecha_digitacion')->nullable();
                });
            }

            if (! Schema::hasTable('autorizaciones_improcedentes')) {
                Schema::create('autorizaciones_improcedentes', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('expediente_id');
                    $table->string('tipo_autorizacion', 10);
                    $table->timestamp('fecha_rechazo')->nullable();
                    $table->string('documento', 100)->nullable();
                    $table->string('observaciones', 250)->nullable();
                    $table->foreignId('usuario_id')->nullable();
                    $table->timestamp('fecha_digitacion')->nullable();
                    $table->boolean('esta_activo')->default(true);
                });
            }

            if (! Schema::hasTable('auth_user')) {

                if (! Schema::hasTable('tipos_documento_identidad')) {
                    Schema::create('tipos_documento_identidad', function (Blueprint $table) {
                        $table->id();
                        $table->string('codigo', 20)->unique();
                        $table->string('nombre', 100);
                        $table->boolean('esta_activo')->default(true);
                        $table->boolean('es_para_natural')->default(true);
                        $table->boolean('es_para_juridica')->default(true);
                    });
                }

                if (! Schema::hasTable('personas_documentos')) {
                    Schema::create('personas_documentos', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('persona_id');
                        $table->foreignId('tipo_documento_identidad_id');
                        $table->string('numero_documento', 20);
                    });
                }

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

            if (! Schema::hasTable('estados')) {
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
