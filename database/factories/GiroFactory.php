<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Giro;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Giro>
 */
class GiroFactory extends Factory
{
    protected $model = Giro::class;

    public function definition(): array
    {
        return [
            'ciiu_id' => fake()->numberBetween(1000, 9999),
            'nombre' => fake()->words(3, true),
            'esta_activo' => true,
            'fecha_digitacion' => now(),
            'usuario_id' => 1,
        ];
    }
}
