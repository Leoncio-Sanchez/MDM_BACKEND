<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\LicenciaFuncionamiento;
use App\Repositories\LicenciaRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LicenciaService
{
    public function __construct(
        private readonly LicenciaRepository $repository,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function crear(array $data, ?int $usuarioId = null): LicenciaFuncionamiento
    {
        return DB::transaction(function () use ($data, $usuarioId): LicenciaFuncionamiento {
            $numeroLicencia = (int) $data['numero_licencia'];

            if (LicenciaFuncionamiento::where('numero_licencia', $numeroLicencia)->exists()) {
                throw new HttpResponseException(
                    response()->json(['error' => "Ya existe una licencia con el número {$numeroLicencia}."], 409)
                );
            }

            if (! empty($data['numero_recibo_pago'])) {
                $recibo = (string) $data['numero_recibo_pago'];
                if (LicenciaFuncionamiento::where('numero_recibo_pago', $recibo)->exists()) {
                    throw new HttpResponseException(
                        response()->json(['error' => "Ya existe una licencia con el número de recibo de pago {$recibo}."], 409)
                    );
                }
            }

            $expedienteId = isset($data['expediente_id']) ? (int) $data['expediente_id'] : null;
            if ($expedienteId !== null) {
                $denegada = DB::table('autorizaciones_improcedentes')
                    ->where('expediente_id', $expedienteId)
                    ->where('tipo_autorizacion', 'LF')
                    ->exists();

                if ($denegada) {
                    throw new HttpResponseException(
                        response()->json(['error' => 'El expediente registra licencia denegada.'], 409)
                    );
                }
            }

            $licencia = new LicenciaFuncionamiento;
            $licencia->uuid = (string) ($data['uuid'] ?? Str::uuid()->toString());
            $licencia->expediente_id = $expedienteId;
            $licencia->tipo_licencia_id = (int) $data['tipo_licencia_id'];
            $licencia->numero_licencia = $numeroLicencia;
            $licencia->fecha_emision = ! empty($data['fecha_emision']) ? Carbon::parse($data['fecha_emision'])->toDateString() : Carbon::now()->toDateString();
            $licencia->titular_id = isset($data['titular_id']) ? (int) $data['titular_id'] : null;
            $licencia->conductor_id = isset($data['conductor_id']) ? (int) $data['conductor_id'] : null;
            $licencia->licencia_principal_id = isset($data['licencia_principal_id']) ? (int) $data['licencia_principal_id'] : null;
            $licencia->nombre_comercial = (string) $data['nombre_comercial'];
            $licencia->es_vigencia_indeterminada = (bool) ($data['es_vigencia_indeterminada'] ?? true);
            $licencia->fecha_inicio_vigencia = ! empty($data['fecha_inicio_vigencia']) ? Carbon::parse($data['fecha_inicio_vigencia'])->toDateString() : null;
            $licencia->fecha_fin_vigencia = ! empty($data['fecha_fin_vigencia']) ? Carbon::parse($data['fecha_fin_vigencia'])->toDateString() : null;
            $licencia->nivel_riesgo_id = isset($data['nivel_riesgo_id']) ? (int) $data['nivel_riesgo_id'] : null;
            $licencia->actividad = (string) ($data['actividad'] ?? '');
            $licencia->direccion = (string) ($data['direccion'] ?? '');
            $licencia->hora_desde = isset($data['hora_desde']) ? (int) $data['hora_desde'] : 0;
            $licencia->hora_hasta = isset($data['hora_hasta']) ? (int) $data['hora_hasta'] : 0;
            $licencia->resolucion_numero = (string) ($data['resolucion_numero'] ?? '');
            $licencia->zonificacion_id = isset($data['zonificacion_id']) ? (int) $data['zonificacion_id'] : null;
            $licencia->area = isset($data['area']) ? (float) $data['area'] : 0;
            $licencia->numero_recibo_pago = (string) ($data['numero_recibo_pago'] ?? '');
            $licencia->observaciones = isset($data['observaciones']) ? (string) $data['observaciones'] : null;
            $licencia->se_puede_publicar = (bool) ($data['se_puede_publicar'] ?? false);
            $licencia->usuario_id = $usuarioId;
            $licencia->fecha_digitacion = Carbon::now('UTC')->toDateTimeString();
            $licencia->save();

            if (! empty($data['giros']) && is_array($data['giros'])) {
                foreach ($data['giros'] as $giroItem) {
                    $giroId = is_array($giroItem) ? ($giroItem['giro_id'] ?? null) : $giroItem;
                    if ($giroId) {
                        DB::table('licencias_funcionamiento_giros')->insert([
                            'licencia_funcionamiento_id' => $licencia->id,
                            'giro_id' => (int) $giroId,
                            'usuario_id' => $usuarioId,
                            'fecha_digitacion' => Carbon::now('UTC')->toDateTimeString(),
                        ]);
                    }
                }
            }

            return $licencia;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function actualizar(int $id, array $data): LicenciaFuncionamiento
    {
        return DB::transaction(function () use ($id, $data): LicenciaFuncionamiento {
            $licencia = LicenciaFuncionamiento::find($id);
            if (! $licencia) {
                throw new HttpResponseException(
                    response()->json(['error' => 'La licencia de funcionamiento no existe.'], 404)
                );
            }

            if (isset($data['numero_licencia']) && (int) $data['numero_licencia'] !== (int) $licencia->numero_licencia) {
                $num = (int) $data['numero_licencia'];
                if (LicenciaFuncionamiento::where('numero_licencia', $num)->where('id', '!=', $id)->exists()) {
                    throw new HttpResponseException(
                        response()->json(['error' => "Ya existe una licencia con el número {$num}."], 409)
                    );
                }
                $licencia->numero_licencia = $num;
            }

            if (isset($data['numero_recibo_pago']) && (string) $data['numero_recibo_pago'] !== (string) $licencia->numero_recibo_pago) {
                $recibo = (string) $data['numero_recibo_pago'];
                if (LicenciaFuncionamiento::where('numero_recibo_pago', $recibo)->where('id', '!=', $id)->exists()) {
                    throw new HttpResponseException(
                        response()->json(['error' => "Ya existe una licencia con el número de recibo de pago {$recibo}."], 409)
                    );
                }
                $licencia->numero_recibo_pago = $recibo;
            }

            if (array_key_exists('nombre_comercial', $data)) {
                $licencia->nombre_comercial = (string) $data['nombre_comercial'];
            }
            if (array_key_exists('tipo_licencia_id', $data)) {
                $licencia->tipo_licencia_id = (int) $data['tipo_licencia_id'];
            }
            if (array_key_exists('titular_id', $data)) {
                $licencia->titular_id = $data['titular_id'] !== null ? (int) $data['titular_id'] : null;
            }
            if (array_key_exists('conductor_id', $data)) {
                $licencia->conductor_id = $data['conductor_id'] !== null ? (int) $data['conductor_id'] : null;
            }
            if (array_key_exists('expediente_id', $data)) {
                $licencia->expediente_id = $data['expediente_id'] !== null ? (int) $data['expediente_id'] : null;
            }
            if (array_key_exists('nivel_riesgo_id', $data)) {
                $licencia->nivel_riesgo_id = $data['nivel_riesgo_id'] !== null ? (int) $data['nivel_riesgo_id'] : null;
            }
            if (array_key_exists('zonificacion_id', $data)) {
                $licencia->zonificacion_id = $data['zonificacion_id'] !== null ? (int) $data['zonificacion_id'] : null;
            }
            if (array_key_exists('actividad', $data)) {
                $licencia->actividad = (string) $data['actividad'];
            }
            if (array_key_exists('direccion', $data)) {
                $licencia->direccion = (string) $data['direccion'];
            }
            if (array_key_exists('area', $data)) {
                $licencia->area = (float) $data['area'];
            }
            if (array_key_exists('resolucion_numero', $data)) {
                $licencia->resolucion_numero = (string) $data['resolucion_numero'];
            }
            if (array_key_exists('observaciones', $data)) {
                $licencia->observaciones = $data['observaciones'] !== null ? (string) $data['observaciones'] : null;
            }
            if (array_key_exists('se_puede_publicar', $data)) {
                $licencia->se_puede_publicar = (bool) $data['se_puede_publicar'];
            }

            $licencia->save();

            if (isset($data['giros']) && is_array($data['giros'])) {
                DB::table('licencias_funcionamiento_giros')->where('licencia_funcionamiento_id', $id)->delete();
                foreach ($data['giros'] as $giroItem) {
                    $giroId = is_array($giroItem) ? ($giroItem['giro_id'] ?? null) : $giroItem;
                    if ($giroId) {
                        DB::table('licencias_funcionamiento_giros')->insert([
                            'licencia_funcionamiento_id' => $id,
                            'giro_id' => (int) $giroId,
                            'fecha_digitacion' => Carbon::now('UTC')->toDateTimeString(),
                        ]);
                    }
                }
            }

            return $licencia;
        });
    }

    public function eliminar(int $id): void
    {
        DB::transaction(function () use ($id): void {
            $licencia = LicenciaFuncionamiento::find($id);
            if (! $licencia) {
                throw new HttpResponseException(
                    response()->json(['error' => 'La licencia de funcionamiento no existe.'], 404)
                );
            }

            if (LicenciaFuncionamiento::where('licencia_principal_id', $id)->exists()) {
                throw new HttpResponseException(
                    response()->json(['error' => 'No se puede eliminar la licencia porque tiene licencias dependientes asociadas.'], 409)
                );
            }

            DB::table('licencias_funcionamiento_estados')->where('licencia_funcionamiento_id', $id)->delete();
            DB::table('licencias_funcionamiento_giros')->where('licencia_funcionamiento_id', $id)->delete();
            DB::table('licencias_funcionamiento_archivos')->where('licencia_funcionamiento_id', $id)->delete();

            $licencia->delete();
        });
    }

    /**
     * @param  array<string, mixed>  $filtros
     * @return Collection<int, LicenciaFuncionamiento>
     */
    public function consulta(array $filtros): Collection
    {
        $query = LicenciaFuncionamiento::query()->orderBy('id', 'desc');

        $numero = $filtros['numero_licencia'] ?? ($filtros['numero'] ?? null);
        if ($numero !== null && trim((string) $numero) !== '') {
            $query->where('numero_licencia', (int) $numero);
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
                'se_puede_emitir_licencia' => false,
                'expediente_id' => null,
                'mensaje' => 'El expediente no existe, primero debe ingresarlo.',
            ];
        }

        $denegada = DB::table('autorizaciones_improcedentes')
            ->where('expediente_id', $expediente->id)
            ->where('tipo_autorizacion', 'LF')
            ->exists();

        if ($denegada) {
            return [
                'se_puede_emitir_licencia' => false,
                'expediente_id' => $expediente->id,
                'mensaje' => 'El expediente registra licencia denegada.',
            ];
        }

        $licencia = LicenciaFuncionamiento::where('expediente_id', $expediente->id)->first();
        if ($licencia) {
            return [
                'se_puede_emitir_licencia' => false,
                'expediente_id' => $expediente->id,
                'mensaje' => "El expediente ya registra la licencia número {$licencia->numero_licencia}.",
            ];
        }

        return [
            'se_puede_emitir_licencia' => true,
            'expediente_id' => $expediente->id,
            'mensaje' => '',
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function inactivar(int $licenciaId, array $data, ?int $usuarioId = null): array
    {
        $licencia = LicenciaFuncionamiento::find($licenciaId);
        if (! $licencia) {
            throw new HttpResponseException(
                response()->json(['error' => 'La licencia de funcionamiento no existe.'], 404)
            );
        }

        $estadoId = (int) ($data['estado_id'] ?? 0);
        $existe = DB::table('licencias_funcionamiento_estados')
            ->where('licencia_funcionamiento_id', $licenciaId)
            ->where('estado_id', $estadoId)
            ->exists();

        if ($existe) {
            throw new HttpResponseException(
                response()->json(['error' => 'Ya existe un registro con el mismo par licencia + estado.'], 409)
            );
        }

        $fechaEstado = ! empty($data['fecha_estado']) ? Carbon::parse($data['fecha_estado'])->toDateString() : Carbon::now()->toDateString();
        $documento = trim((string) ($data['documento'] ?? ''));
        $observaciones = trim((string) ($data['observaciones'] ?? ($data['motivo'] ?? '')));
        $ahora = Carbon::now('UTC');

        $id = DB::table('licencias_funcionamiento_estados')->insertGetId([
            'licencia_funcionamiento_id' => $licenciaId,
            'estado_id' => $estadoId,
            'fecha_estado' => $fechaEstado,
            'documento' => $documento,
            'observaciones' => $observaciones,
            'usuario_id' => $usuarioId,
            'fecha_digitacion' => $ahora->toDateTimeString(),
        ]);

        return [
            'id' => $id,
            'licencia_funcionamiento_id' => $licenciaId,
            'estado_id' => $estadoId,
            'fecha_estado' => $fechaEstado,
            'documento' => $documento,
            'observaciones' => $observaciones,
            'usuario_id' => $usuarioId,
            'fecha_digitacion' => $ahora->setTimezone('America/Lima')->format('Y-m-d\TH:i:s.uP'),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listarEstados(int $licenciaId): array
    {
        if (! LicenciaFuncionamiento::where('id', $licenciaId)->exists()) {
            throw new HttpResponseException(
                response()->json(['error' => 'La licencia de funcionamiento no existe.'], 404)
            );
        }

        $rows = DB::table('licencias_funcionamiento_estados as lfe')
            ->join('estados as est', 'lfe.estado_id', '=', 'est.id')
            ->where('lfe.licencia_funcionamiento_id', $licenciaId)
            ->select([
                'lfe.id',
                'lfe.licencia_funcionamiento_id',
                'lfe.estado_id',
                'lfe.fecha_estado',
                'lfe.documento',
                'lfe.observaciones',
                'lfe.usuario_id',
                'lfe.fecha_digitacion',
                'est.nombre as estado_nombre',
                'est.es_para_lf',
                'est.es_para_itse',
                'est.esta_activo',
            ])
            ->orderBy('lfe.fecha_digitacion', 'asc')
            ->get();

        return $rows->map(function ($row) {
            $arr = (array) $row;
            if (! empty($arr['fecha_digitacion'])) {
                $arr['fecha_digitacion'] = Carbon::parse($arr['fecha_digitacion'])->setTimezone('America/Lima')->format('Y-m-d\TH:i:s.uP');
            }
            if (! empty($arr['fecha_estado'])) {
                $arr['fecha_estado'] = Carbon::parse($arr['fecha_estado'])->toDateString();
            }

            return $arr;
        })->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function giros(int $licenciaId): array
    {
        if (! LicenciaFuncionamiento::where('id', $licenciaId)->exists()) {
            throw new HttpResponseException(
                response()->json(['error' => 'La licencia de funcionamiento no existe.'], 404)
            );
        }

        $rows = DB::table('licencias_funcionamiento_giros as lfg')
            ->join('giros as g', 'g.id', '=', 'lfg.giro_id')
            ->where('lfg.licencia_funcionamiento_id', $licenciaId)
            ->select([
                'lfg.id',
                'lfg.licencia_funcionamiento_id',
                'lfg.giro_id',
                'g.ciiu_id',
                'g.nombre',
            ])
            ->get();

        return $rows->map(fn ($r) => (array) $r)->all();
    }

    public function notificar(int $licenciaId, string $fechaNotificacion): void
    {
        $licencia = LicenciaFuncionamiento::find($licenciaId);
        if (! $licencia) {
            throw new HttpResponseException(
                response()->json(['error' => 'La licencia de funcionamiento no existe.'], 404)
            );
        }

        $fechaNotif = Carbon::parse($fechaNotificacion);
        if ($licencia->fecha_emision && $fechaNotif->lt(Carbon::parse($licencia->fecha_emision))) {
            throw new HttpResponseException(
                response()->json(['error' => 'La fecha de notificación no puede ser anterior a la fecha de emisión.'], 409)
            );
        }

        $licencia->fecha_notificacion = $fechaNotif->toDateTimeString();
        $licencia->save();
    }

    /**
     * @param  array<string, mixed>  $filtros
     * @return array<int, array<string, mixed>>
     */
    public function generarReporte(array $filtros): array
    {
        return $this->repository->generarReporte($filtros);
    }
}
