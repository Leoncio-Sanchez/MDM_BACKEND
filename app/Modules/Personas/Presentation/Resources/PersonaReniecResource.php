<?php

declare(strict_types=1);

namespace App\Modules\Personas\Presentation\Resources;

use App\Modules\Personas\Application\DTOs\PersonaReniecDTO;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PersonaReniecDTO */
class PersonaReniecResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'dni' => $this->dni,
            'nombres' => $this->nombres,
            'apellido_paterno' => $this->apellidoPaterno,
            'apellido_materno' => $this->apellidoMaterno,
        ];
    }
}
