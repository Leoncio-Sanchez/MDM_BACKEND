<?php

declare(strict_types=1);

namespace App\Modules\Personas\Presentation\Resources;

use App\Modules\Personas\Domain\Entities\Persona;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Persona */
class PersonaResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'documentos' => PersonaDocumentoResource::collection($this->documentos)->resolve(),
            'tipo_persona' => $this->tipoPersona->value,
            'sexo' => $this->sexo->value,
            'apellido_paterno' => $this->apellidoPaterno,
            'apellido_materno' => $this->apellidoMaterno,
            'nombres' => $this->nombres,
            'direccion' => $this->direccion,
            'distrito' => $this->distrito,
            'provincia' => $this->provincia,
            'departamento' => $this->departamento,
            'telefono' => $this->telefono,
            'correo_electronico' => $this->correoElectronico,
            'fecha_creacion' => $this->fechaCreacion->setTimezone(new \DateTimeZone('America/Lima'))->format('Y-m-d\TH:i:s.uP'),
            'fecha_actualizacion' => $this->fechaActualizacion->setTimezone(new \DateTimeZone('America/Lima'))->format('Y-m-d\TH:i:s.uP'),
            'user' => $this->userId,
        ];
    }
}
