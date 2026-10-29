<?php

declare(strict_types=1);

namespace App\Modules\ConsultaPublica\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Crear la cuenta o restablecer la contraseña: documento + un expediente suyo + contraseña nueva */
final class CuentaCiudadanoRequest extends FormRequest
{
    public const CLAVE_MINIMO = 8;

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'documento' => ['required', 'string', 'regex:/^[0-9A-Za-z]{8,12}$/'],
            'numero_expediente' => ['required', 'integer', 'min:1', 'max:2147483647'],
            // Al menos una letra y un número
            'clave' => ['required', 'string', 'min:'.self::CLAVE_MINIMO, 'max:100', 'regex:/[A-Za-z]/', 'regex:/[0-9]/', 'confirmed'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'documento.required' => 'Ingrese su DNI o RUC.',
            'documento.regex' => 'Ingrese un DNI (8 dígitos), RUC (11 dígitos) o carné de extranjería válido.',
            'numero_expediente.required' => 'Ingrese el número de uno de sus expedientes.',
            'numero_expediente.integer' => 'El número de expediente solo debe tener dígitos.',
            'numero_expediente.min' => 'Ingrese un número de expediente válido.',
            'numero_expediente.max' => 'Ingrese un número de expediente válido.',
            'clave.required' => 'Elija una contraseña.',
            'clave.min' => 'La contraseña debe tener al menos '.self::CLAVE_MINIMO.' caracteres.',
            'clave.max' => 'La contraseña es demasiado larga.',
            'clave.regex' => 'La contraseña debe tener letras y números.',
            'clave.confirmed' => 'Las contraseñas no coinciden.',
        ];
    }
}
