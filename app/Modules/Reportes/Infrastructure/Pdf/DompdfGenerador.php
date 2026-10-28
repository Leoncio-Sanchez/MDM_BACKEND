<?php

declare(strict_types=1);

namespace App\Modules\Reportes\Infrastructure\Pdf;

use App\Modules\Reportes\Domain\Contracts\GeneradorPdfInterface;
use Barryvdh\DomPDF\Facade\Pdf;

final readonly class DompdfGenerador implements GeneradorPdfInterface
{
    public function generar(string $plantilla, array $datos, bool $horizontal = false): string
    {
        $pdf = Pdf::loadView($plantilla, $datos)
            ->setPaper('a4', $horizontal ? 'landscape' : 'portrait')
            // Solo las letras usadas de la fuente: el archivo pasa de ~1,4 MB a unos cientos de KB
            ->setOption('isFontSubsettingEnabled', true);

        $dompdf = $pdf->getDomPDF();
        $dompdf->render();

        // "Página X de Y" en todas las hojas (el total solo se conoce al terminar de armar el PDF)
        $canvas = $dompdf->getCanvas();
        $fuente = $dompdf->getFontMetrics()->getFont('DejaVu Sans')
            ?? throw new \RuntimeException('La fuente DejaVu Sans no está disponible para generar el PDF.');
        $canvas->page_text(
            $canvas->get_width() - 110,
            $canvas->get_height() - 28,
            'Página {PAGE_NUM} de {PAGE_COUNT}',
            $fuente,
            7,
            [0.35, 0.35, 0.35],
        );

        return (string) $dompdf->output();
    }
}
