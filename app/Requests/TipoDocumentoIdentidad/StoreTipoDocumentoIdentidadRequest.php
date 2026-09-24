<?php

declare(strict_types=1);

namespace App\Requests\TipoDocumentoIdentidad;

use Illuminate\Foundation\Http\FormRequest;

class StoreTipoDocumentoIdentidadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'codigo' => 'required|string|max:20',
            'nombre' => 'required|string|max:100',
            'esta_activo' => 'nullable|boolean',
            'es_para_natural' => 'nullable|boolean',
            'es_para_juridica' => 'nullable|boolean',
        ];
    }
}
