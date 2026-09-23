<?php
declare(strict_types=1);
namespace App\Modules\Licencias\Infrastructure\Persistence\Models;
use App\Shared\Infrastructure\Concerns\ReadOnlyTrait;
use Illuminate\Database\Eloquent\Model;
final class TipoLicenciaReadModel extends Model {
    use ReadOnlyTrait;
    protected $table = 'tipos_licencia';
    protected $primaryKey = 'id';
    public $timestamps = false;
    public $incrementing = false;
    /** @var list<string> */
    protected $guarded = ['*'];
}
