<?php

declare(strict_types=1);

namespace App\Modules\ConsultaPublica\Domain\Contracts;

use App\Modules\ConsultaPublica\Domain\ValueObjects\TramitePublico;

/**
 * Busca trámites por el documento (DNI/RUC/CE) de una de sus partes y, si se indica, su número.
 * Si el documento no corresponde al trámite, no devuelve nada. Sin número: todos los del
 * documento ("Mis trámites" del ciudadano con cuenta).
 */
interface ConsultaPublicaRepositoryInterface
{
    /**
     * Partes: solicitante o representante. El número de expediente puede repetirse entre años.
     *
     * @return list<TramitePublico>
     */
    public function expedientes(?int $numero, string $documento): array;

    /**
     * Partes: titular o conductor.
     *
     * @return list<TramitePublico>
     */
    public function licencias(?int $numero, string $documento): array;

    /**
     * Partes: titular o conductor.
     *
     * @return list<TramitePublico>
     */
    public function itse(?int $numero, string $documento): array;
}
