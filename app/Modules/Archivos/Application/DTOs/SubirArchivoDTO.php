<?php

declare(strict_types=1);

namespace App\Modules\Archivos\Application\DTOs;

use Illuminate\Http\UploadedFile;

final readonly class SubirArchivoDTO
{
    public function __construct(
        public int $parentId,
        public UploadedFile $archivo,
        public int $usuarioId,
        public string $carpetaBase,
    ) {}
}
