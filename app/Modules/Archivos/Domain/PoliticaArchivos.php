<?php

declare(strict_types=1);

namespace App\Modules\Archivos\Domain;

/**
 * Qué archivos se aceptan como adjuntos (actas, fotos de inspección, documentos del trámite).
 *
 * Se limita a formatos de documentos e imágenes: un HTML o SVG subido como adjunto podría
 * ejecutar código al abrirse desde el sistema.
 */
final class PoliticaArchivos
{
    /** @var list<string> */
    public const EXTENSIONES = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx'];

    /** Se abren en el navegador; el resto se descarga. */
    public const EXTENSIONES_EN_LINEA = ['pdf', 'jpg', 'jpeg', 'png'];

    /** 10 MB: alcanza para fotos de celular y actas escaneadas. */
    public const TAMANIO_MAXIMO_KB = 10240;

    public static function seAbreEnNavegador(string $extension): bool
    {
        return in_array(strtolower($extension), self::EXTENSIONES_EN_LINEA, true);
    }
}
