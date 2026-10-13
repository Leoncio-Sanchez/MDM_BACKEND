<?php

declare(strict_types=1);

namespace App\Modules\UnidadesOrganicas\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Crear (POST) o reemplazar (PUT) una unidad orgánica. */
final class GuardarUnidadOrganicaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** La entidad guarda código y sigla en mayúsculas: se normalizan antes de validar que el código sea único. */
    protected function prepareForValidation(): void
    {
        foreach (['codigo', 'sigla'] as $campo) {
            if (is_string($this->input($campo))) {
                $this->merge([$campo => mb_strtoupper(trim($this->input($campo)))]);
            }
        }
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        // Límites = columnas de unidades_organicas (codigo 20, nombre 100, sigla 20)
        $unico = Rule::unique('unidades_organicas', 'codigo');
        if ($this->route('id') !== null) {
            $unico = $unico->ignore((int) $this->route('id'));
        }

        return [
            'codigo' => ['required', 'string', 'max:20', $unico],
            'nombre' => ['required', 'string', 'max:100'],
            'sigla' => ['nullable', 'string', 'max:20'],
            'esta_activo' => ['sometimes', 'boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'codigo.required' => 'El código es obligatorio.',
            'codigo.unique' => 'Ya existe una unidad orgánica con ese código.',
            'codigo.max' => 'El código no puede superar 20 caracteres.',
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede superar 100 caracteres.',
            'sigla.max' => 'La sigla no puede superar 20 caracteres.',
        ];
    }
}
