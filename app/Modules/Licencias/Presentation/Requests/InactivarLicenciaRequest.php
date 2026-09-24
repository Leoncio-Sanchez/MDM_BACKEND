<?php

declare(strict_types=1);

namespace App\Modules\Licencias\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class InactivarLicenciaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'licencia_id' => ['required', 'integer'],
            'motivo' => ['required', 'string'],
        ];
    }
}
