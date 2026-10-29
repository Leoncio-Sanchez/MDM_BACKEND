<?php

declare(strict_types=1);

namespace App\Modules\Inspecciones\Domain\ValueObjects;

use App\Modules\Inspecciones\Domain\Exceptions\ActaInvalida;
use DateTimeImmutable;

/**
 * Lo que el inspector constata en la visita.
 *
 * El resultado no lo elige el inspector: sale del checklist. Basta que un punto no cumpla
 * para que el local no sea seguro (Observado en la 1.ª visita, Desaprobado en la reinspección).
 */
final readonly class DatosActa
{
    /** Grupos del checklist (columna en BD => cómo se nombra en los mensajes) */
    public const GRUPOS = [
        'extintores' => 'extintores',
        'senalizacion' => 'señalización',
        'luces_emergencia' => 'luces de emergencia',
        'tableros_electricos' => 'tableros eléctricos',
    ];

    public const PLAZOS_SUBSANACION = [2, 3];

    public string $numeroActa;

    public ?string $observaciones;

    /**
     * @param  array<string, EstadoChecklist>  $checklist  una entrada por cada clave de GRUPOS
     */
    public function __construct(
        string $numeroActa,
        public DateTimeImmutable $fechaVisita,
        public float $areaM2,
        public int $aforo,
        public array $checklist,
        ?string $observaciones,
        public ?int $plazoSubsanacionDias,
    ) {
        $this->numeroActa = mb_strtoupper(trim($numeroActa));
        $observaciones = $observaciones === null ? '' : trim($observaciones);
        $this->observaciones = $observaciones === '' ? null : $observaciones;

        if ($fechaVisita > new DateTimeImmutable) {
            throw ActaInvalida::fechaFutura();
        }
        if ($this->hayIncumplimientos() && $this->observaciones === null) {
            throw ActaInvalida::faltaObservaciones();
        }
    }

    public function hayIncumplimientos(): bool
    {
        return in_array(EstadoChecklist::NoCumple, $this->checklist, true);
    }

    /** 1.ª visita: Aprobado u Observado; reinspección: Aprobado o Desaprobado */
    public function resultado(int $visita): ResultadoActa
    {
        if (! $this->hayIncumplimientos()) {
            return ResultadoActa::Aprobado;
        }

        return $visita === 1 ? ResultadoActa::Observado : ResultadoActa::Desaprobado;
    }

    /**
     * Fotos que sustentan el acta: la fachada siempre; extintores y tableros salvo que
     * el grupo no aplique al local.
     *
     * @return array<string, string> tipo => cómo se nombra en los mensajes
     */
    public function fotosObligatorias(): array
    {
        $fotos = [TipoFotoActa::Fachada->value => 'la fachada'];
        if ($this->checklist['extintores'] !== EstadoChecklist::NoAplica) {
            $fotos[TipoFotoActa::Extintores->value] = 'los extintores';
        }
        if ($this->checklist['tableros_electricos'] !== EstadoChecklist::NoAplica) {
            $fotos[TipoFotoActa::Tableros->value] = 'los tableros eléctricos';
        }

        return $fotos;
    }
}
