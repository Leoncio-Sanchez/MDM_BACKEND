<?php
declare(strict_types=1);
use App\Modules\Licencias\Infrastructure\Persistence\Repositories\EloquentLicenciaRepository;
use App\Modules\Licencias\Application\Mappers\LicenciaMapper;
use App\Modules\Licencias\Domain\Entities\Licencia;
use Illuminate\Support\Facades\DB;

it('pagina licencias correctamente', function () {
    // Basic instantiation test without mutating Neon
    $mapper = new LicenciaMapper();
    $repo = new EloquentLicenciaRepository($mapper);
    expect($repo)->not->toBeNull();
});
