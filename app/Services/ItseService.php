<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Itse;
use App\Repositories\ItseRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ItseService
{
    public function __construct(
        private readonly ItseRepository $repository
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function crear(array $data, ?int $usuarioId): Itse
    {
        return DB::transaction(function () use ($data, $usuarioId): Itse {
            $expedienteId = (int) $data['expediente_id'];
            if (! DB::table('expedientes')->where('id', $expedienteId)->exists()) {
                throw new HttpResponseException(
                    response()->json(['error' => 'El expediente indicado no existe.'], 400)
                );
            }

            if (DB::table('autorizaciones_improcedentes')
                ->where('expediente_id', $expedienteId)
                ->where('tipo_autorizacion', 'ITSE')
                ->exists()) {
                throw new HttpResponseException(
                    response()->json(['error' => 'El expediente tiene autorización improcedente para ITSE; no se puede emitir.'], 409)
                );
            }

            $existente = Itse::where('expediente_id', $expedienteId)->first();
            if ($existente) {
                throw new HttpResponseException(
                    response()->json(['error' => "El expediente ya registra el ITSE número {$existente->numero_itse}."], 409)
                );
            }

            $numeroItse = (int) $data['numero_itse'];
            if (Itse::where('numero_itse', $numeroItse)->exists()) {
                throw new HttpResponseException(
                    response()->json(['error' => 'El número de la ITSE ya existe.'], 409)
                );
            }

            $recibo = (string) $data['numero_recibo_pago'];
            if (Itse::where('numero_recibo_pago', $recibo)->exists()) {
                throw new HttpResponseException(
                    response()->json(['error' => "El número de recibo de pago \"{$recibo}\" ya se encuentra registrado en la tabla itse."], 409)
                );
            }

            $itse = new Itse;
            $itse->uuid = (string) Str::uuid();
            $itse->numero_itse = $numeroItse;
            $itse->fecha_expedicion = ! empty($data['fecha_expedicion']) ? Carbon::parse($data['fecha_expedicion'])->toDateString() : null;
            $itse->fecha_solicitud_renovacion = ! empty($data['fecha_solicitud_renovacion']) ? Carbon::parse($data['fecha_solicitud_renovacion'])->toDateString() : null;
            $itse->fecha_caducidad = ! empty($data['fecha_caducidad']) ? Carbon::parse($data['fecha_caducidad'])->toDateString() : null;
            $itse->nombre_comercial = (string) ($data['nombre_comercial'] ?? '');
            $itse->direccion = (string) ($data['direccion'] ?? '');
            $itse->resolucion_numero = (string) ($data['resolucion_numero'] ?? '');
            $itse->area = isset($data['area']) ? (float) $data['area'] : 0.0;
            $itse->capacidad_aforo = isset($data['capacidad_aforo']) ? (int) $data['capacidad_aforo'] : 0;
            $itse->numero_recibo_pago = $recibo;
            $itse->observaciones = isset($data['observaciones']) ? (string) $data['observaciones'] : '';
            $itse->se_puede_publicar = (bool) ($data['se_puede_publicar'] ?? false);
            $itse->fecha_notificacion = ! empty($data['fecha_notificacion']) ? Carbon::parse($data['fecha_notificacion'])->toDateString() : null;
            $itse->tipo_itse_id = (int) $data['tipo_itse_id'];
            $itse->expediente_id = $expedienteId;
            $itse->itse_principal_id = isset($data['itse_principal_id']) ? (int) $data['itse_principal_id'] : null;
            $itse->nivel_riesgo_id = (int) $data['nivel_riesgo_id'];
            $itse->conductor_id = (int) $data['conductor_id'];
            $itse->titular_id = (int) $data['titular_id'];
            $itse->usuario_id = $usuarioId;
            $itse->fecha_digitacion = Carbon::now('UTC')->toDateTimeString();
            $itse->save();

            if (! empty($data['giros']) && is_array($data['giros'])) {
                foreach ($data['giros'] as $giroItem) {
                    $giroId = is_array($giroItem) ? ($giroItem['giro_id'] ?? null) : $giroItem;
                    if ($giroId) {
                        DB::table('itse_giros')->insert([
                            'itse_id' => $itse->id,
                            'giro_id' => (int) $giroId,
                            'usuario_id' => $usuarioId,
                            'fecha_digitacion' => Carbon::now('UTC')->toDateTimeString(),
                        ]);
                    }
                }
            }

            return $itse;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function actualizar(int $id, array $data): Itse
    {
        return DB::transaction(function () use ($id, $data): Itse {
            $itse = Itse::find($id);
            if (! $itse) {
                throw new HttpResponseException(
                    response()->json(['error' => 'La ITSE no existe.'], 404)
                );
            }

            if (isset($data['expediente_id'])) {
                $expedienteId = (int) $data['expediente_id'];
                if (! DB::table('expedientes')->where('id', $expedienteId)->exists()) {
                    throw new HttpResponseException(
                        response()->json(['error' => 'El expediente indicado no existe.'], 400)
                    );
                }

                if (DB::table('autorizaciones_improcedentes')
                    ->where('expediente_id', $expedienteId)
                    ->where('tipo_autorizacion', 'ITSE')
                    ->exists()) {
                    throw new HttpResponseException(
                        response()->json(['error' => 'El expediente tiene autorización improcedente para ITSE; no se puede emitir.'], 409)
                    );
                }

                $existente = Itse::where('expediente_id', $expedienteId)->where('id', '!=', $id)->first();
                if ($existente) {
                    throw new HttpResponseException(
                        response()->json(['error' => "El expediente ya registra el ITSE número {$existente->numero_itse}."], 409)
                    );
                }
                $itse->expediente_id = $expedienteId;
            }

            if (isset($data['numero_itse']) && (int) $data['numero_itse'] !== (int) $itse->numero_itse) {
                $num = (int) $data['numero_itse'];
                if (Itse::where('numero_itse', $num)->where('id', '!=', $id)->exists()) {
                    throw new HttpResponseException(
                        response()->json(['error' => 'El número de la ITSE ya existe.'], 409)
                    );
                }
                $itse->numero_itse = $num;
            }

            if (isset($data['numero_recibo_pago']) && (string) $data['numero_recibo_pago'] !== (string) $itse->numero_recibo_pago) {
                $recibo = (string) $data['numero_recibo_pago'];
                if (Itse::where('numero_recibo_pago', $recibo)->where('id', '!=', $id)->exists()) {
                    throw new HttpResponseException(
                        response()->json(['error' => "El número de recibo de pago \"{$recibo}\" ya se encuentra registrado en la tabla itse."], 409)
                    );
                }
                $itse->numero_recibo_pago = $recibo;
            }

            if (array_key_exists('fecha_expedicion', $data)) {
                $itse->fecha_expedicion = ! empty($data['fecha_expedicion']) ? Carbon::parse($data['fecha_expedicion'])->toDateString() : null;
            }
            if (array_key_exists('fecha_solicitud_renovacion', $data)) {
                $itse->fecha_solicitud_renovacion = ! empty($data['fecha_solicitud_renovacion']) ? Carbon::parse($data['fecha_solicitud_renovacion'])->toDateString() : null;
            }
            if (array_key_exists('fecha_caducidad', $data)) {
                $itse->fecha_caducidad = ! empty($data['fecha_caducidad']) ? Carbon::parse($data['fecha_caducidad'])->toDateString() : null;
            }
            if (array_key_exists('nombre_comercial', $data)) {
                $itse->nombre_comercial = (string) $data['nombre_comercial'];
            }
            if (array_key_exists('direccion', $data)) {
                $itse->direccion = (string) $data['direccion'];
            }
            if (array_key_exists('resolucion_numero', $data)) {
                $itse->resolucion_numero = (string) $data['resolucion_numero'];
            }
            if (array_key_exists('area', $data)) {
                $itse->area = (float) $data['area'];
            }
            if (array_key_exists('capacidad_aforo', $data)) {
                $itse->capacidad_aforo = (int) $data['capacidad_aforo'];
            }
            if (array_key_exists('observaciones', $data)) {
                $itse->observaciones = (string) ($data['observaciones'] ?? '');
            }
            if (array_key_exists('se_puede_publicar', $data)) {
                $itse->se_puede_publicar = (bool) $data['se_puede_publicar'];
            }
            if (array_key_exists('tipo_itse_id', $data)) {
                $itse->tipo_itse_id = (int) $data['tipo_itse_id'];
            }
            if (array_key_exists('itse_principal_id', $data)) {
                $itse->itse_principal_id = $data['itse_principal_id'] !== null ? (int) $data['itse_principal_id'] : null;
            }
            if (array_key_exists('nivel_riesgo_id', $data)) {
                $itse->nivel_riesgo_id = (int) $data['nivel_riesgo_id'];
            }
            if (array_key_exists('conductor_id', $data)) {
                $itse->conductor_id = (int) $data['conductor_id'];
            }
            if (array_key_exists('titular_id', $data)) {
                $itse->titular_id = (int) $data['titular_id'];
            }

            $itse->save();

            if (array_key_exists('giros', $data) && is_array($data['giros'])) {
                DB::table('itse_giros')->where('itse_id', $id)->delete();
                foreach ($data['giros'] as $giroItem) {
                    $giroId = is_array($giroItem) ? ($giroItem['giro_id'] ?? null) : $giroItem;
                    if ($giroId) {
                        DB::table('itse_giros')->insert([
                            'itse_id' => $itse->id,
                            'giro_id' => (int) $giroId,
                            'usuario_id' => $itse->usuario_id,
                            'fecha_digitacion' => Carbon::now('UTC')->toDateTimeString(),
                        ]);
                    }
                }
            }

            return $itse;
        });
    }

    public function eliminar(int $id): void
    {
        DB::transaction(function () use ($id): void {
            $itse = Itse::find($id);
            if (! $itse) {
                throw new HttpResponseException(
                    response()->json(['error' => 'La ITSE no existe.'], 404)
                );
            }

            if (Itse::where('itse_principal_id', $id)->exists()) {
                throw new HttpResponseException(
                    response()->json(['error' => 'No se puede eliminar la ITSE: tiene ITSE dependientes asociadas. Primero debe eliminar las ITSE dependientes.'], 409)
                );
            }

            DB::table('itse_estados')->where('itse_id', $id)->delete();
            DB::table('itse_giros')->where('itse_id', $id)->delete();
            DB::table('itse_archivos')->where('itse_id', $id)->delete();

            $itse->delete();
        });
    }

    public function obtener(int $id): Itse
    {
        $itse = Itse::find($id);
        if (! $itse) {
            throw new HttpResponseException(
                response()->json(['error' => 'La ITSE no existe.'], 404)
            );
        }

        return $itse;
    }

    /**
     * @param  array<string, mixed>  $filtros
     * @return Collection<int, Itse>
     */
    public function consulta(array $filtros): Collection
    {
        $query = Itse::query()->orderBy('id', 'desc');

        $numero = $filtros['numero_itse'] ?? ($filtros['numero'] ?? null);
        if ($numero !== null && trim((string) $numero) !== '') {
            $query->where('numero_itse', (int) $numero);
        }

        return $query->get();
    }

    /**
     * @return Collection<int, Itse>
     */
    public function porRenovar(?string $fechaDesde, ?string $fechaHasta): Collection
    {
        $query = Itse::query()->orderBy('fecha_solicitud_renovacion', 'asc');

        if ($fechaDesde !== null && trim($fechaDesde) !== '') {
            $query->where('fecha_solicitud_renovacion', '>=', $fechaDesde);
        }
        if ($fechaHasta !== null && trim($fechaHasta) !== '') {
            $query->where('fecha_solicitud_renovacion', '<=', $fechaHasta);
        }

        return $query->get();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function buscar(string $filtro, string $valor): array
    {
        try {
            return $this->repository->buscarConFiltros($filtro, $valor);
        } catch (\InvalidArgumentException $e) {
            throw new HttpResponseException(
                response()->json(['error' => $e->getMessage()], 400)
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function verificarExpediente(int $numeroExpediente, int $anio): array
    {
        $driver = DB::connection()->getDriverName();
        $query = DB::table('expedientes')->where('numero_expediente', $numeroExpediente);

        if ($driver === 'pgsql') {
            $query->whereRaw('EXTRACT(YEAR FROM fecha_recepcion) = ?', [$anio]);
        } else {
            $query->whereRaw("strftime('%Y', fecha_recepcion) = ?", [(string) $anio]);
        }

        $expediente = $query->first();

        if (! $expediente) {
            return [
                'se_puede_emitir_itse' => false,
                'expediente_id' => null,
                'mensaje' => 'El expediente no existe, primero debe ingresarlo.',
            ];
        }

        $denegada = DB::table('autorizaciones_improcedentes')
            ->where('expediente_id', $expediente->id)
            ->where('tipo_autorizacion', 'ITSE')
            ->exists();

        if ($denegada) {
            return [
                'se_puede_emitir_itse' => false,
                'expediente_id' => $expediente->id,
                'mensaje' => 'El expediente registra ITSE denegado.',
            ];
        }

        $itse = Itse::where('expediente_id', $expediente->id)->first();
        if ($itse) {
            return [
                'se_puede_emitir_itse' => false,
                'expediente_id' => $expediente->id,
                'mensaje' => "El expediente ya registra el ITSE número {$itse->numero_itse}.",
            ];
        }

        return [
            'se_puede_emitir_itse' => true,
            'expediente_id' => $expediente->id,
            'mensaje' => '',
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function inactivar(int $itseId, array $data, ?int $usuarioId): array
    {
        $itse = Itse::find($itseId);
        if (! $itse) {
            throw new HttpResponseException(
                response()->json(['error' => 'La ITSE no existe.'], 404)
            );
        }

        $estadoId = (int) $data['estado_id'];

        if (DB::table('itse_estados')
            ->where('itse_id', $itseId)
            ->where('estado_id', $estadoId)
            ->exists()) {
            throw new HttpResponseException(
                response()->json(['error' => 'Ya existe un registro para este ITSE con el mismo estado.'], 409)
            );
        }

        $ahora = Carbon::now('UTC');
        $id = DB::table('itse_estados')->insertGetId([
            'itse_id' => $itseId,
            'estado_id' => $estadoId,
            'fecha_estado' => Carbon::parse($data['fecha_estado'])->toDateString(),
            'documento' => trim((string) ($data['documento'] ?? '')),
            'observaciones' => trim((string) ($data['observaciones'] ?? '')),
            'usuario_id' => $usuarioId,
            'fecha_digitacion' => $ahora->toDateTimeString(),
        ]);

        return [
            'id' => $id,
            'mensaje' => 'Inactivación del ITSE registrada correctamente.',
        ];
    }

    public function notificar(int $itseId, string $fechaNotificacion): void
    {
        $itse = Itse::find($itseId);
        if (! $itse) {
            throw new HttpResponseException(
                response()->json(['error' => 'El ITSE no existe.'], 404)
            );
        }

        $fechaNotif = Carbon::parse($fechaNotificacion)->toDateString();
        $fechaExp = Carbon::parse($itse->fecha_expedicion)->toDateString();

        if ($fechaNotif < $fechaExp) {
            throw new HttpResponseException(
                response()->json(['error' => "La fecha de notificación no puede ser anterior a la fecha de expedición ({$fechaExp})."], 409)
            );
        }

        $itse->fecha_notificacion = $fechaNotif;
        $itse->save();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listarEstados(int $itseId): array
    {
        if (! Itse::where('id', $itseId)->exists()) {
            throw new HttpResponseException(
                response()->json(['error' => 'La ITSE no existe.'], 404)
            );
        }

        return $this->repository->listarEstados($itseId);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listarGiros(int $itseId): array
    {
        if (! Itse::where('id', $itseId)->exists()) {
            throw new HttpResponseException(
                response()->json(['error' => 'La ITSE no existe.'], 404)
            );
        }

        $rows = DB::table('itse_giros as ig')
            ->join('giros as g', 'ig.giro_id', '=', 'g.id')
            ->where('ig.itse_id', $itseId)
            ->select([
                'g.id',
                'g.ciiu_id',
                'g.nombre',
            ])
            ->orderBy('g.id', 'asc')
            ->get();

        return $rows->map(fn ($r) => (array) $r)->all();
    }

    /**
     * @return array<int, mixed>
     */
    public function inspectores(int $itseId): array
    {
        return [];
    }
}
