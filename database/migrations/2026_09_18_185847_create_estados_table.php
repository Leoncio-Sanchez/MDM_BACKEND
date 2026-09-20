<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('estados')) Schema::create('estados', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->boolean('es_para_lf')->default(true);
            $table->boolean('es_para_itse')->default(true);
            $table->boolean('esta_activo')->default(false);
            $table->index('esta_activo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estados');
    }
};
