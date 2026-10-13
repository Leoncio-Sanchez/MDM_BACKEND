<?php

declare(strict_types=1);

namespace App\Modules\TipoProcedimientoTupa\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ActualizarTipoProcedimientoTupaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** La entidad guarda el código en mayúsculas: se normaliza antes de validar que sea único. */
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('codigo'))) {
            $this->merge(['codigo' => strtoupper(trim($this->input('codigo')))]);
        }
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        // Actualización parcial: solo se validan los campos enviados.
        $diasAlerta = ['nullable', 'integer', 'min:0'];
        if ($this->filled('plazo_atencion_dias')) {
            $diasAlerta[] = 'lte:plazo_atencion_dias';
        }

        return [
            'codigo' => ['nullable', 'string', 'max:30', Rule::unique('tipos_procedimiento_tupa', 'codigo')->ignore((int) $this->route('id'))],
            'nombre' => 'nullable|string|max:200',
            'plazo_atencion_dias' => 'nullable|integer|min:1',
            'dias_alerta_vencimiento' => $diasAlerta,
            'esta_activo' => 'nullable|boolean',
            'requiere_lf' => 'nullable|boolean',
            'requiere_itse' => 'nullable|boolean',
            'unidad_organica_id' => 'nullable|integer|exists:unidades_organicas,id',
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'codigo.unique' => 'Ya existe un procedimiento con ese código.',
            'codigo.max' => 'El código no puede superar 30 caracteres.',
            'nombre.max' => 'El nombre no puede superar 200 caracteres.',
            'plazo_atencion_dias.min' => 'El plazo de atención debe ser de al menos 1 día.',
            'dias_alerta_vencimiento.lte' => 'Los días de alerta no pueden superar el plazo de atención.',
            'unidad_organica_id.exists' => 'La unidad orgánica seleccionada no existe.',
        ];
    }
}
