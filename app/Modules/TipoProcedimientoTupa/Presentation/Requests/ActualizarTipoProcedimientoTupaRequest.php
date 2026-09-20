<?php

declare(strict_types=1);

namespace App\Modules\TipoProcedimientoTupa\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ActualizarTipoProcedimientoTupaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'codigo' => 'nullable|string|max:50',
            'nombre' => 'nullable|string|max:250',
            'plazo_atencion_dias' => 'nullable|integer|min:0',
            'dias_alerta_vencimiento' => 'nullable|integer|min:0',
            'esta_activo' => 'nullable|boolean',
            'requiere_lf' => 'nullable|boolean',
            'requiere_itse' => 'nullable|boolean',
            'unidad_organica_id' => 'nullable|integer',
        ];
    }
}
