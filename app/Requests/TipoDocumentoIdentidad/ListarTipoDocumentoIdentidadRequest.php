<?php

declare(strict_types=1);

namespace App\Requests\TipoDocumentoIdentidad;

use Illuminate\Foundation\Http\FormRequest;

class ListarTipoDocumentoIdentidadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [];
    }
}
