<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Zonificacion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Zonificacion>
 */
class ZonificacionFactory extends Factory
{
    protected $model = Zonificacion::class;

    public function definition(): array
    {
        return [
            'codigo' => strtoupper(fake()->unique()->lexify('Z??')),
            'nombre' => fake()->words(3, true),
            'esta_activo' => true,
            'fecha_digitacion' => now(),
            'usuario_id' => 1,
        ];
    }
}
