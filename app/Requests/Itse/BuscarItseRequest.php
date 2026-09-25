<?php

declare(strict_types=1);

namespace App\Requests\Itse;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class BuscarItseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'filtro' => ['required', 'string', 'in:ID,NUMERO,EXPEDIENTE,NOMBRE_COMERCIAL,FECHA_EXPEDICION,FECHA_EMISION,NOMBRES_TITULAR,RUC_TITULAR,NOMBRES_CONDUCTOR,DIRECCION,RECIBO_PAGO,RESOLUCION_NUMERO'],
            'valor' => ['required', 'string'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        $errors = $validator->errors();
        if ($errors->has('filtro')) {
            throw new HttpResponseException(
                response()->json(['error' => "El parámetro 'filtro' es obligatorio."], 400)
            );
        }

        if ($errors->has('valor')) {
            throw new HttpResponseException(
                response()->json(['error' => "El parámetro 'valor' es obligatorio."], 400)
            );
        }

        $firstError = $errors->first();
        throw new HttpResponseException(
            response()->json(['error' => $firstError], 400)
        );
    }
}
