<?php

declare(strict_types=1);

namespace App\Requests\Itse;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CrearItseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'numero_itse' => ['required', 'integer'],
            'fecha_expedicion' => ['required', 'date'],
            'fecha_solicitud_renovacion' => ['required', 'date'],
            'fecha_caducidad' => ['required', 'date'],
            'nombre_comercial' => ['required', 'string', 'max:250'],
            'direccion' => ['required', 'string', 'max:250'],
            'resolucion_numero' => ['required', 'string', 'max:50'],
            'area' => ['required'],
            'capacidad_aforo' => ['required', 'integer', 'min:0'],
            'numero_recibo_pago' => ['required', 'string', 'max:20'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
            'se_puede_publicar' => ['sometimes', 'boolean'],
            'tipo_itse_id' => ['required', 'integer', 'in:1,2'],
            'expediente_id' => ['required', 'integer'],
            'itse_principal_id' => ['nullable', 'integer'],
            'nivel_riesgo_id' => ['required', 'integer'],
            'conductor_id' => ['required', 'integer'],
            'titular_id' => ['required', 'integer'],
            'giros' => ['sometimes', 'array'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        $firstError = $validator->errors()->first();
        throw new HttpResponseException(
            response()->json(['error' => $firstError], 400)
        );
    }
}
