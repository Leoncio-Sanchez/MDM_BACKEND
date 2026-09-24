<?php

declare(strict_types=1);

namespace App\Requests\TipoDocumentoIdentidad;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTipoDocumentoIdentidadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'codigo' => 'sometimes|string|max:20',
            'nombre' => 'sometimes|string|max:100',
            'esta_activo' => 'sometimes|boolean',
            'es_para_natural' => 'sometimes|boolean',
            'es_para_juridica' => 'sometimes|boolean',
        ];
    }
}
