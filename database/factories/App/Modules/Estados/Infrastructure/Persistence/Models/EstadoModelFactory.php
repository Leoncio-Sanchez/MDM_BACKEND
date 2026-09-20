<?php

declare(strict_types=1);

namespace Database\Factories\App\Modules\Estados\Infrastructure\Persistence\Models;

use App\Modules\Estados\Infrastructure\Persistence\Models\EstadoModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class EstadoModelFactory extends Factory
{
    protected $model = EstadoModel::class;

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
