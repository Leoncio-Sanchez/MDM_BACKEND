<?php

declare(strict_types=1);

namespace App\Requests\TipoProcedimientoTupa;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTipoProcedimientoTupaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('unidad_organica') && ! $this->has('unidad_organica_id')) {
            $this->merge(['unidad_organica_id' => $this->input('unidad_organica')]);
        }
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'codigo' => ['sometimes', 'nullable', 'string', 'max:50'],
            'nombre' => ['sometimes', 'nullable', 'string', 'max:250'],
            'plazo_atencion_dias' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'dias_alerta_vencimiento' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'esta_activo' => ['sometimes', 'nullable', 'boolean'],
            'requiere_lf' => ['sometimes', 'nullable', 'boolean'],
            'requiere_itse' => ['sometimes', 'nullable', 'boolean'],
            'unidad_organica_id' => ['sometimes', 'nullable', 'integer'],
            'unidad_organica' => ['sometimes', 'nullable', 'integer'],
        ];
    }
}
