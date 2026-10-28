<?php

declare(strict_types=1);

namespace App\Modules\Configuracion\Domain\ValueObjects;

/** Textos del encabezado y la firma de los informes formales en PDF. */
final readonly class ConfiguracionInforme
{
    /** Campo => clave en configuracion_sistema */
    public const CLAVES = [
        'entidad' => 'informe.entidad',
        'nombreAnio' => 'informe.nombre_anio',
        'gerencia' => 'informe.gerencia',
        'siglas' => 'informe.siglas',
        'firmanteNombre' => 'informe.firmante_nombre',
        'firmanteCargo' => 'informe.firmante_cargo',
    ];

    public function __construct(
        public string $entidad,
        // "Nombre Oficial del Año" (lo define el Gobierno cada enero)
        public string $nombreAnio,
        public string $gerencia,
        // Para el correlativo: INFORME N° 012-2026-{siglas}
        public string $siglas,
        public string $firmanteNombre,
        public string $firmanteCargo,
    ) {}

    /** @param array<string, string> $valores clave en configuracion_sistema => valor */
    public static function desdeClaves(array $valores): self
    {
        $v = fn (string $campo) => trim($valores[self::CLAVES[$campo]] ?? '');

        return new self(
            entidad: $v('entidad'),
            nombreAnio: $v('nombreAnio'),
            gerencia: $v('gerencia'),
            siglas: $v('siglas'),
            firmanteNombre: $v('firmanteNombre'),
            firmanteCargo: $v('firmanteCargo'),
        );
    }

    /** @return array<string, string> clave en configuracion_sistema => valor */
    public function aClaves(): array
    {
        $claves = [];
        foreach (self::CLAVES as $campo => $clave) {
            $claves[$clave] = $this->{$campo};
        }

        return $claves;
    }
}
