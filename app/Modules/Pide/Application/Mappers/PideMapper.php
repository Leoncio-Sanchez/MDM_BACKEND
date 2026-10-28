<?php

declare(strict_types=1);

namespace App\Modules\Pide\Application\Mappers;

use App\Modules\Pide\Domain\Exceptions\DocumentoNoEncontrado;
use App\Modules\Pide\Domain\Exceptions\ReniecError;
use App\Modules\Pide\Domain\ValueObjects\ContribuyenteSunat;
use App\Modules\Pide\Domain\ValueObjects\PersonaReniec;

/**
 * Convierte la respuesta cruda de PIDE en solo los datos que usa el sistema.
 * Se lee de forma defensiva: un campo que no llega queda vacío en lugar de fallar.
 */
final class PideMapper
{
    /**
     * Formato PIDE RENIEC: consultarResponse.return { coResultado, deResultado, datosPersona { prenombres,
     * apPrimer, apSegundo, direccion, ubigeo "DEPARTAMENTO/PROVINCIA/DISTRITO", foto, ... } }
     *
     * @param array<string, mixed> $respuesta
     *
     * @throws DocumentoNoEncontrado si el DNI no figura en RENIEC
     * @throws ReniecError si PIDE rechazó la consulta (credenciales, servicio)
     */
    public static function personaDesdeReniec(string $dni, array $respuesta): PersonaReniec
    {
        $retorno = self::arreglo(self::arreglo($respuesta['consultarResponse'] ?? [])['return'] ?? []);
        $datos = self::arreglo($retorno['datosPersona'] ?? []);

        if (self::texto($datos, 'prenombres') === '') {
            // Los códigos 0xxx son respuestas de la consulta (p. ej. DNI inexistente);
            // el resto indica un problema del servicio o de las credenciales.
            $codigo = self::texto($retorno, 'coResultado');
            if ($codigo === '' || str_starts_with($codigo, '0')) {
                throw DocumentoNoEncontrado::dni();
            }
            throw ReniecError::rechazada($codigo, self::texto($retorno, 'deResultado'));
        }

        [$departamento, $provincia, $distrito] = array_pad(array_map('trim', explode('/', self::texto($datos, 'ubigeo'))), 3, '');

        return new PersonaReniec(
            dni: $dni,
            nombres: self::texto($datos, 'prenombres'),
            apellidoPaterno: self::texto($datos, 'apPrimer'),
            apellidoMaterno: self::texto($datos, 'apSegundo'),
            direccion: self::texto($datos, 'direccion'),
            departamento: $departamento,
            provincia: $provincia,
            distrito: $distrito,
        );
    }

    /**
     * Formato PIDE SUNAT (DatosPrincipales): getDatosPrincipalesResponse.return { ddp_numruc, ddp_nombre,
     * desc_estado, desc_flag22, desc_tipvia, ddp_nomvia, ddp_numer1, ddp_inter1, desc_tipzon, ddp_nomzon,
     * desc_dep, desc_prov, desc_dist, ... }
     *
     * @param array<string, mixed> $respuesta
     *
     * @throws DocumentoNoEncontrado si el RUC no figura en SUNAT
     */
    public static function contribuyenteDesdeSunat(string $ruc, array $respuesta): ContribuyenteSunat
    {
        $datos = self::arreglo(self::arreglo($respuesta['getDatosPrincipalesResponse'] ?? [])['return'] ?? []);

        if (self::texto($datos, 'ddp_nombre') === '') {
            throw DocumentoNoEncontrado::ruc();
        }

        // Dirección armada con las partes que tengan valor (SUNAT usa "-" para "sin dato")
        $parte = fn (string $campo, string $prefijo = '') => ($v = self::texto($datos, $campo)) !== '' && $v !== '-' ? $prefijo.$v : '';
        $direccion = implode(' ', array_filter([
            $parte('desc_tipvia'), $parte('ddp_nomvia'), $parte('ddp_numer1', 'NRO. '), $parte('ddp_inter1', 'INT. '),
            $parte('desc_tipzon'), $parte('ddp_nomzon'),
        ]));

        return new ContribuyenteSunat(
            ruc: $ruc,
            razonSocial: self::texto($datos, 'ddp_nombre'),
            estado: self::primero($datos, ['desc_estado', 'ddp_estado']),
            condicion: self::primero($datos, ['desc_flag22', 'ddp_condicion', 'ddp_flag22']),
            direccion: $direccion,
            departamento: self::texto($datos, 'desc_dep'),
            provincia: self::texto($datos, 'desc_prov'),
            distrito: self::texto($datos, 'desc_dist'),
        );
    }

    /** @return array<string, mixed> */
    private static function arreglo(mixed $valor): array
    {
        return is_array($valor) ? $valor : [];
    }

    /** @param array<string, mixed> $datos */
    private static function texto(array $datos, string $campo): string
    {
        $valor = $datos[$campo] ?? '';

        return is_scalar($valor) ? trim((string) $valor) : '';
    }

    /**
     * @param array<string, mixed> $datos
     * @param list<string> $campos
     */
    private static function primero(array $datos, array $campos): string
    {
        foreach ($campos as $campo) {
            if (($v = self::texto($datos, $campo)) !== '') {
                return $v;
            }
        }

        return '';
    }
}
