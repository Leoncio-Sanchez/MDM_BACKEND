<?php

declare(strict_types=1);

namespace App\Controllers\Auth;

use App\Models\User;
use App\Resources\UserAuthResource;
use Illuminate\Http\Request;

final class UserController
{
    public function me(Request $request): UserAuthResource
    {
        /** @var User|null $user */
        $user = $request->user();

        if ($user === null) {
            abort(401, 'Unauthenticated.');
        }

        return new UserAuthResource($user);
    }
}
