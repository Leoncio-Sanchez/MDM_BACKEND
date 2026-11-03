<?php

declare(strict_types=1);

namespace App\Modules\Personas\Application\Mappers;

use App\Modules\Personas\Domain\Entities\Persona;
use App\Modules\Personas\Domain\Entities\PersonaDocumento;
use App\Modules\Personas\Domain\ValueObjects\Sexo;
use App\Modules\Personas\Domain\ValueObjects\TipoPersona;
use App\Modules\Personas\Infrastructure\Persistence\Models\PersonaModel;
use Carbon\CarbonImmutable;

final class PersonaMapper
{
    public function toDomain(PersonaModel $model): Persona
    {
        return new Persona(
            id: $model->id,
            tipoPersona: TipoPersona::from($model->tipo_persona),
            // Un dato heredado vacío o desconocido no debe impedir listar a la persona
            sexo: Sexo::tryFrom(strtoupper(trim($model->sexo))) ?? Sexo::PrefieroNoDecir,
            apellidoPaterno: $model->apellido_paterno,
            apellidoMaterno: $model->apellido_materno,
            nombres: $model->nombres,
            direccion: $model->direccion,
            distrito: $model->distrito,
            provincia: $model->provincia,
            departamento: $model->departamento,
            telefono: $model->telefono,
            correoElectronico: $model->correo_electronico,
            fechaCreacion: CarbonImmutable::parse($model->fecha_creacion),
            fechaActualizacion: CarbonImmutable::parse($model->fecha_actualizacion),
            userId: $model->user_id,
            documentos: $model->relationLoaded('documentos') ? array_map(
                fn ($d) => new PersonaDocumento(
                    $d->id,
                    $d->persona_id,
                    $d->tipo_documento_identidad_id,
                    $d->numero_documento
                ),
                $model->documentos->all()
            ) : [],
        );
    }

    /** @return array<string, mixed> */
    public function toPersistence(Persona $persona): array
    {
        return [
            'tipo_persona' => $persona->tipoPersona->value,
            'sexo' => $persona->sexo->value,
            'apellido_paterno' => $persona->apellidoPaterno,
            'apellido_materno' => $persona->apellidoMaterno,
            'nombres' => $persona->nombres,
            'direccion' => $persona->direccion,
            'distrito' => $persona->distrito,
            'provincia' => $persona->provincia,
            'departamento' => $persona->departamento,
            'telefono' => $persona->telefono,
            'correo_electronico' => $persona->correoElectronico,
            'fecha_creacion' => $persona->fechaCreacion->toDateTimeString(),
            'fecha_actualizacion' => $persona->fechaActualizacion->toDateTimeString(),
            'user_id' => $persona->userId,
        ];
    }
}
