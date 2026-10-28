<?php

declare(strict_types=1);

namespace App\Modules\Reportes\Domain\Contracts;

interface GeneradorPdfInterface
{
    /**
     * @param  array<string, mixed>  $datos
     * @return string contenido binario del PDF
     */
    public function generar(string $plantilla, array $datos, bool $horizontal = false): string;
}
