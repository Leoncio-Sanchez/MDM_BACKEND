<?php
declare(strict_types=1);

namespace App\Modules\Personas\Infrastructure\Persistence\Repositories;

use App\Modules\Personas\Domain\Contracts\PersonaDocumentoRepositoryInterface;
use App\Modules\Personas\Domain\Entities\PersonaDocumento;
use App\Modules\Personas\Infrastructure\Persistence\Models\PersonaDocumentoModel;

final class EloquentPersonaDocumentoRepository implements PersonaDocumentoRepositoryInterface
{
    /** @return PersonaDocumento[] */
    public function listarPorPersona(int $personaId): array
    {
        $models = PersonaDocumentoModel::where('persona_id', $personaId)->get();

        return $models->map(fn (PersonaDocumentoModel $m) => new PersonaDocumento(
            id: $m->id,
            personaId: $m->persona_id,
            tipoDocumentoIdentidadId: $m->tipo_documento_identidad_id,
            numeroDocumento: $m->numero_documento,
        ))->all();
    }

    public function crear(PersonaDocumento $documento): PersonaDocumento
    {
        $model = PersonaDocumentoModel::create([
            'persona_id' => $documento->personaId,
            'tipo_documento_identidad_id' => $documento->tipoDocumentoIdentidadId,
            'numero_documento' => $documento->numeroDocumento,
        ]);

        return new PersonaDocumento(
            id: $model->id,
            personaId: $model->persona_id,
            tipoDocumentoIdentidadId: $model->tipo_documento_identidad_id,
            numeroDocumento: $model->numero_documento,
        );
    }

    public function eliminarPorPersona(int $personaId): void
    {
        PersonaDocumentoModel::where('persona_id', $personaId)->delete();
    }
}
