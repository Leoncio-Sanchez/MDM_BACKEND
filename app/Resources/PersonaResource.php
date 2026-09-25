<?php

declare(strict_types=1);

namespace App\Resources;

use App\Models\Persona;
use Carbon\Carbon;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Persona
 */
class PersonaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $fechaCreacionStr = null;
        if ($this->fecha_creacion !== null && $this->fecha_creacion !== '') {
            $c = Carbon::parse($this->fecha_creacion)->setTimezone(new DateTimeZone('America/Lima'));
            $fmt = ((int) $c->format('u')) > 0 ? 'Y-m-d\TH:i:s.uP' : 'Y-m-d\TH:i:sP';
            $fechaCreacionStr = $c->format($fmt);
        }

        $fechaActualizacionStr = null;
        if ($this->fecha_actualizacion !== null && $this->fecha_actualizacion !== '') {
            $c = Carbon::parse($this->fecha_actualizacion)->setTimezone(new DateTimeZone('America/Lima'));
            $fmt = ((int) $c->format('u')) > 0 ? 'Y-m-d\TH:i:s.uP' : 'Y-m-d\TH:i:sP';
            $fechaActualizacionStr = $c->format($fmt);
        }

        return [
            'id' => (int) $this->id,
            'documentos' => PersonaDocumentoResource::collection($this->documentos)->resolve(),
            'tipo_persona' => (string) $this->tipo_persona,
            'sexo' => (string) $this->sexo,
            'apellido_paterno' => $this->apellido_paterno !== null ? (string) $this->apellido_paterno : null,
            'apellido_materno' => $this->apellido_materno !== null ? (string) $this->apellido_materno : null,
            'nombres' => (string) $this->nombres,
            'direccion' => (string) $this->direccion,
            'distrito' => (string) $this->distrito,
            'provincia' => (string) $this->provincia,
            'departamento' => (string) $this->departamento,
            'telefono' => $this->telefono !== null ? (string) $this->telefono : null,
            'correo_electronico' => $this->correo_electronico !== null ? (string) $this->correo_electronico : null,
            'fecha_creacion' => $fechaCreacionStr,
            'fecha_actualizacion' => $fechaActualizacionStr,
            'user' => (int) ($this->user_id ?? 1),
        ];
    }
}
