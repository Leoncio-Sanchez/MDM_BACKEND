<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class PorRenovarItseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<string>> */
    public function rules(): array
    {
        return [
            'fecha_desde' => ['sometimes', 'nullable', 'date'],
            'fecha_hasta' => ['sometimes', 'nullable', 'date'],
        ];
    }
}
