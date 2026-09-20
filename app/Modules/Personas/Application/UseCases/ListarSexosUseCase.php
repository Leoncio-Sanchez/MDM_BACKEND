<?php
declare(strict_types=1);

namespace App\Modules\Personas\Application\UseCases;

use App\Modules\Personas\Domain\ValueObjects\Sexo;

final readonly class ListarSexosUseCase
{
    /** @return array<int, array{value: string, label: string}> */
    public function execute(): array
    {
        return array_map(
            fn (Sexo $s) => ['value' => $s->value, 'label' => $s->name === 'PrefieroNoDecir' ? 'Prefiero no decirlo' : $s->name],
            Sexo::cases(),
        );
    }
}
