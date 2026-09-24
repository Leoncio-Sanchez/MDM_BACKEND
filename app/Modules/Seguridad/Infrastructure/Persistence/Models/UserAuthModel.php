<?php

declare(strict_types=1);

namespace App\Modules\Seguridad\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $password
 * @property string|null $last_login
 * @property bool $is_superuser
 * @property string $username
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property bool $is_staff
 * @property bool $is_active
 * @property string $date_joined
 */
final class UserAuthModel extends Model
{
    protected $table = 'auth_user';

    public $timestamps = false;

    protected $fillable = [
        'username',
        'password',
        'first_name',
        'last_name',
        'email',
        'is_superuser',
        'is_staff',
        'is_active',
        'last_login',
        'date_joined',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'is_superuser' => 'boolean',
            'is_staff' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
