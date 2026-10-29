<?php

declare(strict_types=1);

namespace App\Modules\Inspectores\Application\UseCases;

use App\Modules\Inspectores\Domain\Contracts\InspectorRepositoryInterface;
use App\Modules\Seguridad\Domain\Contracts\PasswordHasherInterface;
use App\Modules\Usuarios\Infrastructure\Persistence\Models\UsuarioModel;

final readonly class ActualizarInspectorUseCase
{
    public function __construct(
        private InspectorRepositoryInterface $repository,
        private PasswordHasherInterface $hasher,
    ) {}

    /**
     * Contraseña vacía o ausente = se conserva la actual.
     *
     * @param  array<string, mixed>  $datos
     */
    public function execute(int $id, array $datos): ?UsuarioModel
    {
        if (! empty($datos['password'])) {
            $datos['password'] = $this->hasher->hash((string) $datos['password']);
        } else {
            unset($datos['password']);
        }

        return $this->repository->actualizar($id, $datos);
    }
}
