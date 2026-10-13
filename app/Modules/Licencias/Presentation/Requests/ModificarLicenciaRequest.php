<?php

declare(strict_types=1);

namespace App\Modules\Licencias\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ModificarLicenciaRequest extends FormRequest
{
    use ReglasDatosLicencia;

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        // PUT reemplaza la licencia completa: se exigen los mismos datos que al crear.
        // El número de licencia es opcional (si no se envía se conserva el actual).
        return [
            'numero_licencia' => ['sometimes', 'integer', 'min:1'],
            ...$this->reglasDatosLicencia(),
        ];
    }
}
