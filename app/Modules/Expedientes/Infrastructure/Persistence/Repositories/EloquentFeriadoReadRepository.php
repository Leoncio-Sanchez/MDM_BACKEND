<?php

declare(strict_types=1);

namespace App\Modules\Expedientes\Infrastructure\Persistence\Repositories;

use App\Modules\Expedientes\Domain\Contracts\FeriadoReadRepositoryInterface;
use App\Modules\Expedientes\Infrastructure\Persistence\Models\FeriadoAnualReadModel;
use App\Modules\Expedientes\Infrastructure\Persistence\Models\FeriadoRecurrenteReadModel;
use DateTimeImmutable;

final readonly class EloquentFeriadoReadRepository implements FeriadoReadRepositoryInterface
{
    public function esFeriado(DateTimeImmutable $fecha): bool
    {
        $dia = (int) $fecha->format('j');
        $mes = (int) $fecha->format('n');

        // Recurrente (mismo día y mes cada año)
        $recurrente = FeriadoRecurrenteReadModel::query()
            ->where('dia', $dia)
            ->where('mes', $mes)
            ->exists();

        if ($recurrente) {
            return true;
        }

        // Anual (fecha específica YYYY-MM-DD en columna 'feriado')
        return FeriadoAnualReadModel::query()
            ->where('feriado', $fecha->format('Y-m-d'))
            ->exists();
    }
}
