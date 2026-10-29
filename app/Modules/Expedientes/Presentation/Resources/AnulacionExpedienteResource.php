<?php

declare(strict_types=1);

namespace App\Modules\Expedientes\Presentation\Resources;

use App\Modules\Expedientes\Domain\ValueObjects\AnulacionExpediente;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AnulacionExpediente
 */
final class AnulacionExpedienteResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'motivo' => $this->motivo,
            'documento' => $this->documento,
            'fecha' => $this->fechaAnulacion->setTimezone(new \DateTimeZone('America/Lima'))->format('Y-m-d\TH:i:sP'),
            'usuario' => $this->usuarioId,
            'usuario_nombre' => $this->usuarioNombre,
        ];
    }

    /** @return array<string, mixed>|null */
    public static function oNulo(?AnulacionExpediente $anulacion): ?array
    {
        return $anulacion !== null ? (new self($anulacion))->resolve() : null;
    }
}
