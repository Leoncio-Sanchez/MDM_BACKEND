<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Persona;
use App\Models\PersonaDocumento;
use App\Repositories\PersonaRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class PersonaService
{
    public function __construct(
        private readonly PersonaRepository $repository,
    ) {}

    /**
     * @return Collection<int, Persona>
     */
    public function listar(): Collection
    {
        return Persona::with(['documentos.tipoDocumentoIdentidad'])
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombres')
            ->get();
    }

    public function obtener(int $id): Persona
    {
        $persona = Persona::with(['documentos.tipoDocumentoIdentidad'])->find($id);

        if (! $persona) {
            throw new HttpResponseException(
                response()->json(['error' => 'No Persona matches the given query.'], 404)
            );
        }

        return $persona;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function crear(array $data, int $userId): Persona
    {
        /** @var array<int, array{tipo_documento_identidad_id: int, numero_documento: string}> $documentosData */
        $documentosData = $data['documentos'] ?? [];

        $this->verificarDocumentosUnicos($documentosData);

        if (($data['tipo_persona'] ?? '') === 'J') {
            $data['apellido_paterno'] = null;
            $data['apellido_materno'] = null;
        }

        $now = Carbon::now('UTC')->toDateTimeString();
        $personaData = Arr::except($data, ['documentos']);
        $personaData['fecha_creacion'] = $now;
        $personaData['fecha_actualizacion'] = $now;
        $personaData['user_id'] = $userId;

        return DB::transaction(function () use ($personaData, $documentosData): Persona {
            /** @var Persona $persona */
            $persona = Persona::create($personaData);

            foreach ($documentosData as $doc) {
                PersonaDocumento::create([
                    'persona_id' => $persona->id,
                    'tipo_documento_identidad_id' => $doc['tipo_documento_identidad_id'],
                    'numero_documento' => $doc['numero_documento'],
                ]);
            }

            return $this->obtener($persona->id);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function actualizar(int $id, array $data): Persona
    {
        $persona = Persona::find($id);
        if (! $persona) {
            throw new HttpResponseException(
                response()->json(['error' => 'No Persona matches the given query.'], 404)
            );
        }

        if (array_key_exists('documentos', $data)) {
            /** @var array<int, array{tipo_documento_identidad_id: int, numero_documento: string}> $documentosData */
            $documentosData = $data['documentos'];
            $this->verificarDocumentosUnicos($documentosData, $id);
        }

        $tipoPersona = $data['tipo_persona'] ?? $persona->tipo_persona;
        if ($tipoPersona === 'J') {
            $data['apellido_paterno'] = null;
            $data['apellido_materno'] = null;
        }

        $data['fecha_actualizacion'] = Carbon::now('UTC')->toDateTimeString();

        return DB::transaction(function () use ($persona, $data): Persona {
            $persona->update(Arr::except($data, ['documentos']));

            if (array_key_exists('documentos', $data)) {
                PersonaDocumento::where('persona_id', $persona->id)->delete();
                /** @var array<int, array{tipo_documento_identidad_id: int, numero_documento: string}> $docs */
                $docs = $data['documentos'];
                foreach ($docs as $doc) {
                    PersonaDocumento::create([
                        'persona_id' => $persona->id,
                        'tipo_documento_identidad_id' => $doc['tipo_documento_identidad_id'],
                        'numero_documento' => $doc['numero_documento'],
                    ]);
                }
            }

            return $this->obtener($persona->id);
        });
    }

    public function eliminar(int $id): void
    {
        $persona = Persona::find($id);
        if (! $persona) {
            throw new HttpResponseException(
                response()->json(['error' => 'No Persona matches the given query.'], 404)
            );
        }

        if ($this->repository->tieneDependientes($id)) {
            throw new HttpResponseException(
                response()->json(['error' => "La persona con ID {$id} tiene dependientes y no puede ser eliminada."], 400)
            );
        }

        DB::transaction(function () use ($persona, $id): void {
            PersonaDocumento::where('persona_id', $id)->delete();
            $persona->delete();
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function buscar(string $filtro, string $valor): array
    {
        return $this->repository->buscarPorFiltro($filtro, $valor);
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public function sexos(): array
    {
        return [
            ['value' => 'M', 'label' => 'Masculino'],
            ['value' => 'F', 'label' => 'Femenino'],
            ['value' => 'X', 'label' => 'Prefiero no decirlo'],
        ];
    }

    /**
     * @return Collection<int, PersonaDocumento>
     */
    public function documentos(int $personaId): Collection
    {
        $existe = DB::table('personas')->where('id', $personaId)->exists();
        if (! $existe) {
            throw new HttpResponseException(
                response()->json(['error' => 'Persona no encontrada.'], 404)
            );
        }

        return PersonaDocumento::with('tipoDocumentoIdentidad')
            ->where('persona_id', $personaId)
            ->get();
    }

    /**
     * @param  array<int, array{tipo_documento_identidad_id: int, numero_documento: string}>  $documentos
     */
    private function verificarDocumentosUnicos(array $documentos, ?int $excluirPersonaId = null): void
    {
        foreach ($documentos as $doc) {
            $query = PersonaDocumento::with(['persona', 'tipoDocumentoIdentidad'])
                ->where('tipo_documento_identidad_id', $doc['tipo_documento_identidad_id'])
                ->where('numero_documento', $doc['numero_documento']);

            if ($excluirPersonaId !== null) {
                $query->where('persona_id', '!=', $excluirPersonaId);
            }

            /** @var PersonaDocumento|null $existente */
            $existente = $query->first();
            if ($existente && $existente->persona) {
                $persona = $existente->persona;
                $partes = array_filter([
                    $persona->apellido_paterno,
                    $persona->apellido_materno,
                    $persona->nombres,
                ]);
                $nombreCompleto = trim(implode(' ', $partes)) ?: $persona->nombres;
                $tipoDoc = $existente->tipoDocumentoIdentidad;
                $tipoNombre = $tipoDoc !== null ? $tipoDoc->nombre : 'DOCUMENTO';

                throw new HttpResponseException(
                    response()->json([
                        'error' => "El número de documento '{$doc['numero_documento']}' ({$tipoNombre}) ya se encuentra asignado a: {$nombreCompleto}.",
                    ], 409)
                );
            }
        }
    }
}
