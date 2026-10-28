<?php

declare(strict_types=1);

namespace App\Modules\Seguridad\Application\UseCases;

use App\Modules\Seguridad\Domain\Contracts\RevisionAccesosRepositoryInterface;
use App\Modules\Seguridad\Domain\ValueObjects\PersonalSinAccesos;

final readonly class ListarPersonalSinAccesosUseCase
{
    public function __construct(private RevisionAccesosRepositoryInterface $revision) {}

    /** @return list<PersonalSinAccesos> */
    public function execute(): array
    {
        return $this->revision->personalSinAccesos();
    }
}
