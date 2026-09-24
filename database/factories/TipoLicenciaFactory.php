<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\TipoLicencia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TipoLicencia>
 */
class TipoLicenciaFactory extends Factory
{
    protected $model = TipoLicencia::class;

    public function definition(): array
    {
        return [
            'codigo' => strtoupper(fake()->unique()->lexify('???')),
            'nombre' => fake()->words(2, true),
            'esta_activo' => true,
        ];
    }
}
