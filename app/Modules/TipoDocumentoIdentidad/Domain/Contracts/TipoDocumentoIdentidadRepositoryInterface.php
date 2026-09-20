<?php

declare(strict_types=1);

namespace App\Modules\TipoDocumentoIdentidad\Domain\Contracts;

use App\Modules\TipoDocumentoIdentidad\Domain\Entities\TipoDocumentoIdentidad;

interface TipoDocumentoIdentidadRepositoryInterface
{
    /** @return TipoDocumentoIdentidad[] */
    public function obtenerTodos(string $tipoPersona): array;

    public function buscarPorId(int $id): ?TipoDocumentoIdentidad;

    public function guardar(TipoDocumentoIdentidad $entity): TipoDocumentoIdentidad;

    public function actualizar(TipoDocumentoIdentidad $entity): TipoDocumentoIdentidad;

    public function eliminar(int $id): void;
}
