<?php
declare(strict_types=1);

namespace App\Modules\Expedientes\Infrastructure\Persistence\Models;

use App\Shared\Infrastructure\Concerns\ReadOnlyTrait;
use Illuminate\Database\Eloquent\Model;

final class UsuarioReadModel extends Model
{
    use ReadOnlyTrait;

    protected $table = 'auth_user';
    public $timestamps = false;
    public $incrementing = false;
    protected $guarded = ['*'];
}
