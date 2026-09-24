<?php

declare(strict_types=1);

namespace App\Modules\Licencias\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class VerificarExpedienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'numero_expediente' => ['required', 'string'],
            'anio' => ['required', 'string'],
        ];
    }
}
