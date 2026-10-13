<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $table = 'auth_user';
    public $timestamps = false;

    // Columnas reales de auth_user (Django). No hay name/role/ruc: el rol se calcula (Seguridad).
    protected $fillable = [
        'username', 'password', 'first_name', 'last_name', 'email',
        'is_superuser', 'is_staff', 'is_active', 'date_joined', 'last_login',
    ];

    protected $hidden = [
        'password',
    ];

    /**
     * Sin cast 'hashed': las contraseñas usan el formato de Django (pbkdf2_sha256, ver
     * DjangoPbkdf2Hasher). Un cast a bcrypt las dejaría imposibles de verificar.
     */
    protected function casts(): array
    {
        return [
            'is_superuser' => 'boolean',
            'is_staff' => 'boolean',
            'is_active' => 'boolean',
            'date_joined' => 'datetime',
            'last_login' => 'datetime',
        ];
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [
            "id" => $this->id,
            "user_id" => (string)$this->id,
            "username" => $this->username,
            "role" => $this->role,
            "ruc" => $this->ruc,
            "token_type" => "access"
        ];
    }
}
