<?php

declare(strict_types=1);

namespace App\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $username
 * @property string|null $email
 */
class UserAuthResource extends JsonResource
{
    /**
     * @return array{email: string, id: int, username: string}
     */
    public function toArray(Request $request): array
    {
        return [
            'email' => (string) ($this->email ?? ''),
            'id' => (int) $this->id,
            'username' => (string) $this->username,
        ];
    }
}
