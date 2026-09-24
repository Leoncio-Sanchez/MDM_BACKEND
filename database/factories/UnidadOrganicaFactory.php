<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\UnidadOrganica;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UnidadOrganica>
 */
class UnidadOrganicaFactory extends Factory
{
    protected $model = UnidadOrganica::class;

    public function definition(): array
    {
        return [
            'codigo' => strtoupper(fake()->unique()->lexify('UO???')),
            'nombre' => fake()->words(3, true),
            'sigla' => strtoupper(fake()->lexify('??')),
            'esta_activo' => true,
        ];
    }
}
