<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Infrastructure\Persistence\Models;

use App\Shared\Infrastructure\Concerns\ReadOnlyTrait;
use Illuminate\Database\Eloquent\Model;

final class PersonaReadModel extends Model
{
    use ReadOnlyTrait;

    protected $table = 'personas';
    public $timestamps = false;
}
