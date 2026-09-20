<?php
declare(strict_types=1);
namespace App\Modules\Licencias\Infrastructure\Persistence\Models;
use App\Modules\Licencias\Infrastructure\Persistence\Concerns\ReadOnlyTrait;
use Illuminate\Database\Eloquent\Model;
final class PersonaReadModel extends Model {
    use ReadOnlyTrait;
    protected $table = 'personas';
    protected $primaryKey = 'id';
    public $timestamps = false;
    public $incrementing = false;
    /** @var list<string> */
    protected $guarded = ['*'];
}
