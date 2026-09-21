<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (app()->environment('testing')) {
            Schema::create('itse', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->integer('numero_itse')->unique();
                $table->date('fecha_expedicion');
                $table->date('fecha_solicitud_renovacion')->nullable();
                $table->date('fecha_caducidad');
                $table->string('nombre_comercial', 250);
                $table->string('direccion', 250);
                $table->string('resolucion_numero', 50);
                $table->decimal('area', 18, 2);
                $table->integer('capacidad_aforo')->default(0);
                $table->string('numero_recibo_pago', 20);
                $table->string('observaciones', 1000)->nullable();
                $table->boolean('se_puede_publicar')->default(false);
                $table->date('fecha_notificacion')->nullable();
                $table->timestamp('fecha_digitacion')->nullable();
                $table->integer('tipo_itse_id');
                $table->foreignId('expediente_id');
                $table->foreignId('itse_principal_id')->nullable();
                $table->foreignId('usuario_id');
                $table->foreignId('nivel_riesgo_id');
                $table->foreignId('conductor_id');
                $table->foreignId('titular_id');
            });

            Schema::create('itse_estados', function (Blueprint $table) {
                $table->id();
                $table->foreignId('itse_id');
                $table->string('estado', 50);
                $table->date('fecha');
                $table->string('observaciones', 1000)->nullable();
                $table->foreignId('usuario_id');
            });
            
        }
    }

    public function down(): void
    {
        if (app()->environment('testing')) {
            Schema::dropIfExists('itse_estados');
            Schema::dropIfExists('itse');
        }
    }
};
