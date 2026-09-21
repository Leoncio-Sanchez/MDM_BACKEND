<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class VerificarExpedienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('api') !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'numero_expediente' => ['required', 'integer'],
            'anio' => ['required', 'integer'],
        ];
    }
}
