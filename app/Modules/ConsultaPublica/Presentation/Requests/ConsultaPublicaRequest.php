<?php

declare(strict_types=1);

namespace App\Modules\ConsultaPublica\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ConsultaPublicaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'tipo' => ['required', 'string', 'in:expediente,licencia,itse'],
            'numero' => ['required', 'integer', 'min:1', 'max:2147483647'],
            // DNI (8), RUC (11) o carné de extranjería (hasta 12, alfanumérico)
            'documento' => ['required', 'string', 'regex:/^[0-9A-Za-z]{8,12}$/'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'tipo.required' => 'Seleccione qué desea consultar.',
            'tipo.in' => 'Seleccione expediente, licencia o certificado ITSE.',
            'numero.required' => 'Ingrese el número del trámite.',
            'numero.integer' => 'El número del trámite solo debe tener dígitos.',
            'numero.min' => 'Ingrese un número de trámite válido.',
            'numero.max' => 'Ingrese un número de trámite válido.',
            'documento.required' => 'Ingrese su DNI o RUC.',
            'documento.regex' => 'Ingrese un DNI (8 dígitos), RUC (11 dígitos) o carné de extranjería válido.',
        ];
    }
}
