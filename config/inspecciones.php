<?php

declare(strict_types=1);

return [
    /*
     * Desde qué fecha de recepción del expediente se exige el acta de inspección Aprobada
     * para emitir el certificado ITSE (AAAA-MM-DD). null = a todos los expedientes.
     */
    'acta_obligatoria_desde' => env('ITSE_ACTA_OBLIGATORIA_DESDE') ?: null,
];
