<?php

declare(strict_types=1);

namespace App\Modules\TipoProcedimientoTupa\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CrearTipoProcedimientoTupaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'codigo' => 'required|string|max:50',
            'nombre' => 'required|string|max:250',
            'plazo_atencion_dias' => 'required|integer|min:0',
            'dias_alerta_vencimiento' => 'required|integer|min:0',
            'esta_activo' => 'nullable|boolean',
            'requiere_lf' => 'nullable|boolean',
            'requiere_itse' => 'nullable|boolean',
            'unidad_organica_id' => 'required|integer',
        ];
    }
}
