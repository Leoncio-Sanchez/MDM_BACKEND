<?php

declare(strict_types=1);

namespace App\Modules\Reportes\Application\UseCases;

use App\Modules\Configuracion\Domain\Contracts\ConfiguracionRepositoryInterface;
use App\Modules\Reportes\Domain\Contracts\GeneradorPdfInterface;
use App\Modules\Reportes\Domain\Contracts\InformesRepositoryInterface;
use App\Modules\Reportes\Domain\Exceptions\InformeInvalido;
use Carbon\CarbonImmutable;

/**
 * Informe mensual de licencias emitidas en PDF (formato definido por el equipo):
 * encabezado institucional, correlativo, resumen ejecutivo, padrón y pie de firma.
 */
final readonly class GenerarInformeLicenciasUseCase
{
    public const TIPO = 'LICENCIAS_MENSUAL';

    private const MESES = ['ENERO', 'FEBRERO', 'MARZO', 'ABRIL', 'MAYO', 'JUNIO', 'JULIO',
        'AGOSTO', 'SETIEMBRE', 'OCTUBRE', 'NOVIEMBRE', 'DICIEMBRE'];

    public function __construct(
        private PadronLicenciasUseCase $padron,
        private InformesRepositoryInterface $informes,
        private ConfiguracionRepositoryInterface $configuracion,
        private GeneradorPdfInterface $pdf,
    ) {}

    /**
     * @return array{nombre_archivo: string, contenido: string, codigo: string, reimpresion: bool}
     *
     * @throws InformeInvalido
     */
    public function execute(string $desde, string $hasta, int $usuarioId): array
    {
        $inicio = CarbonImmutable::parse($desde);
        if ($inicio->day !== 1 || $hasta !== $inicio->endOfMonth()->toDateString()) {
            throw InformeInvalido::noEsMesCompleto();
        }
        if ($inicio->greaterThan(CarbonImmutable::today())) {
            throw InformeInvalido::mesFuturo();
        }

        $datos = $this->padron->execute($desde, $hasta);
        $config = $this->configuracion->informe();
        $informe = $this->informes->emitir(self::TIPO, $desde, $hasta, $usuarioId);
        $codigo = $informe->codigo($config->siglas);
        $mes = self::MESES[$inicio->month - 1].' '.$inicio->year;

        $contenido = $this->pdf->generar('informes.licencias', [
            'config' => $config,
            'informe' => $informe,
            'codigo' => $codigo,
            'mes' => $mes,
            'fechaEmision' => $this->fechaLarga(CarbonImmutable::now()),
            'generado' => CarbonImmutable::now()->setTimezone('America/Lima')->format('d/m/Y H:i'),
            'resumen' => $datos['resumen'],
            'licencias' => $datos['licencias'],
        ], horizontal: true);

        return [
            'nombre_archivo' => sprintf('Informe_%s_licencias_%s.pdf', str_replace('/', '-', $codigo), $inicio->format('Y-m')),
            'contenido' => $contenido,
            'codigo' => $codigo,
            'reimpresion' => $informe->esReimpresion,
        ];
    }

    /** "28 de octubre de 2026" */
    private function fechaLarga(CarbonImmutable $fecha): string
    {
        $f = $fecha->setTimezone('America/Lima');

        return $f->day.' de '.mb_strtolower(self::MESES[$f->month - 1]).' de '.$f->year;
    }
}
