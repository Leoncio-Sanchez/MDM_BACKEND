<?php

declare(strict_types=1);

namespace App\Modules\TipoDocumentoIdentidad\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuardarTipoDocumentoIdentidadRequest extends FormRequest
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
            'esta_activo' => 'required|boolean',
            'es_para_natural' => 'required|boolean',
            'es_para_juridica' => 'required|boolean',
        ];
    }
}
