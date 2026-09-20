<?php

declare(strict_types=1);

namespace App\Modules\Estados\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CrearEstadoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Autenticación se maneja vía middleware Sanctum
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:100'],
            'es_para_lf' => ['sometimes', 'boolean'],
            'es_para_itse' => ['sometimes', 'boolean'],
        ];
    }
}
