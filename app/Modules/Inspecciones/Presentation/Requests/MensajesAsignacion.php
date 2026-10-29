<?php

declare(strict_types=1);

namespace App\Modules\Inspecciones\Presentation\Requests;

final class MensajesAsignacion
{
    public const MENSAJES = [
        'expediente_id.required' => 'Indique el expediente.',
        'inspector_id.required' => 'Elija el inspector.',
        'nombre_comercial.required' => 'Indique el nombre comercial del local.',
        'direccion.required' => 'Indique la dirección del local a inspeccionar.',
        'fecha_programada.date_format' => 'La fecha programada no es válida.',
        'fecha_programada.after_or_equal' => 'La fecha programada no puede ser anterior a hoy.',
        'acompanantes.max' => 'Se permiten como máximo 3 acompañantes por inspección.',
        'acompanantes.*.distinct' => 'Un acompañante está repetido.',
        '*.max' => 'El texto es demasiado largo.',
    ];
}
