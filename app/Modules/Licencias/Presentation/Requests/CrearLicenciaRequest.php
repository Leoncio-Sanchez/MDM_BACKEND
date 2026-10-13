<?php

declare(strict_types=1);

namespace App\Modules\Licencias\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CrearLicenciaRequest extends FormRequest
{
    use ReglasDatosLicencia;

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'numero_licencia' => ['required', 'integer', 'min:1'],
            ...$this->reglasDatosLicencia(),
        ];
    }
}
