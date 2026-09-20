<?php
declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(
    TestCase::class,
    RefreshDatabase::class,
)->in('Feature', __DIR__ . '/../app/Modules/Licencias/Tests/Feature', __DIR__ . '/../app/Modules/TipoLicencia/Tests/Feature');

uses(
    TestCase::class,
)->in('Unit', __DIR__ . '/../app/Modules/Licencias/Tests/Unit', __DIR__ . '/../app/Modules/TipoLicencia/Tests/Unit');
