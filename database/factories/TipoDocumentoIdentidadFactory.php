<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\TipoDocumentoIdentidad;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TipoDocumentoIdentidad>
 */
class TipoDocumentoIdentidadFactory extends Factory
{
    protected $model = TipoDocumentoIdentidad::class;

    public function definition(): array
    {
        return [
            'codigo' => fake()->unique()->numerify('##'),
            'nombre' => fake()->words(2, true),
            'esta_activo' => true,
            'es_para_natural' => true,
            'es_para_juridica' => false,
        ];
    }
}
