<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Estado;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Estado>
 */
class EstadoFactory extends Factory
{
    protected $model = Estado::class;

    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->word(),
            'es_para_lf' => true,
            'es_para_itse' => true,
            'esta_activo' => false,
        ];
    }
}
