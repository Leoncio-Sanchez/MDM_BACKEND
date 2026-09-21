<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class BuscarItseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('api') !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'filtro' => ['required', 'string', 'in:ID,NUMERO,EXPEDIENTE,NOMBRE_COMERCIAL,FECHA_EXPEDICION,FECHA_EMISION,NOMBRES_TITULAR,RUC_TITULAR,NOMBRES_CONDUCTOR,DIRECCION,RECIBO_PAGO,RESOLUCION_NUMERO'],
            'valor' => ['required', 'string'],
        ];
    }
}
