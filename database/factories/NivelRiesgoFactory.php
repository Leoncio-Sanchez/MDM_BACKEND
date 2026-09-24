<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\NivelRiesgo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NivelRiesgo>
 */
class NivelRiesgoFactory extends Factory
{
    protected $model = NivelRiesgo::class;

    public function definition(): array
    {
        return [
            'codigo' => strtoupper(fake()->unique()->lexify('???')),
            'nombre' => fake()->words(2, true),
            'esta_activo' => true,
        ];
    }
}
