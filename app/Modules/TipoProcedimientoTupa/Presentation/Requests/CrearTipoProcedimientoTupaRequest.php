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
        // Límites = columnas de tipos_procedimiento_tupa (codigo varchar 30, nombre varchar 200)
        return [
            'codigo' => 'required|string|max:30|unique:tipos_procedimiento_tupa,codigo',
            'nombre' => 'required|string|max:200',
            'plazo_atencion_dias' => 'required|integer|min:0',
            // La alerta se dispara antes del vencimiento: no puede superar el plazo
            'dias_alerta_vencimiento' => 'required|integer|min:0|lte:plazo_atencion_dias',
            'esta_activo' => 'nullable|boolean',
            'requiere_lf' => 'nullable|boolean',
            'requiere_itse' => 'nullable|boolean',
            'unidad_organica_id' => 'required|integer|exists:unidades_organicas,id',
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'codigo.unique' => 'Ya existe un procedimiento con ese código.',
            'codigo.max' => 'El código no puede superar 30 caracteres.',
            'nombre.max' => 'El nombre no puede superar 200 caracteres.',
            'plazo_atencion_dias.min' => 'El plazo de atención no puede ser negativo.',
            'dias_alerta_vencimiento.lte' => 'Los días de alerta no pueden superar el plazo de atención.',
            'unidad_organica_id.exists' => 'La unidad orgánica seleccionada no existe.',
        ];
    }
}
