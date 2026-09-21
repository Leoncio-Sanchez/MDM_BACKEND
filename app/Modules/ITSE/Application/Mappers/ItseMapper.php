<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Application\Mappers;

use App\Modules\ITSE\Domain\Entities\Itse;

final class ItseMapper
{
    /** @return array<string, mixed> */
    public static function toArray(Itse $itse): array
    {
        return [
            'id' => $itse->id,
            'uuid' => $itse->uuid,
            'numero_itse' => $itse->numeroItse,
            'fecha_expedicion' => $itse->fechaExpedicion->format('Y-m-d'),
            'fecha_solicitud_renovacion' => $itse->fechaSolicitudRenovacion?->format('Y-m-d'),
            'fecha_caducidad' => $itse->fechaCaducidad->format('Y-m-d'),
            'nombre_comercial' => $itse->nombreComercial,
            'direccion' => $itse->direccion,
            'resolucion_numero' => $itse->resolucionNumero,
            'area' => $itse->area,
            'capacidad_aforo' => $itse->capacidadAforo,
            'numero_recibo_pago' => $itse->numeroReciboPago,
            'observaciones' => $itse->observaciones,
            'se_puede_publicar' => $itse->sePuedePublicar,
            'fecha_notificacion' => $itse->fechaNotificacion?->format('Y-m-d'),
            'fecha_digitacion' => $itse->fechaDigitacion->format('Y-m-d H:i:s'),
            'tipo_itse_id' => $itse->tipoItseId,
            'expediente_id' => $itse->expedienteId,
            'itse_principal_id' => $itse->itsePrincipalId,
            'usuario_id' => $itse->usuarioId,
            'nivel_riesgo_id' => $itse->nivelRiesgoId,
            'conductor_id' => $itse->conductorId,
            'titular_id' => $itse->titularId,
        ];
    }
}
