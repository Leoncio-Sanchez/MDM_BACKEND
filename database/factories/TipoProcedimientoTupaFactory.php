<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\TipoProcedimientoTupa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TipoProcedimientoTupa>
 */
class TipoProcedimientoTupaFactory extends Factory
{
    protected $model = TipoProcedimientoTupa::class;

    public function definition(): array
    {
        return [
            'codigo' => strtoupper(fake()->unique()->lexify('TUP???')),
            'nombre' => fake()->words(3, true),
            'plazo_atencion_dias' => fake()->numberBetween(1, 30),
            'dias_alerta_vencimiento' => fake()->numberBetween(1, 5),
            'esta_activo' => true,
            'requiere_lf' => false,
            'requiere_itse' => false,
            'fecha_digitacion' => now(),
            'unidad_organica_id' => 1,
            'usuario_id' => 1,
        ];
    }
}
