<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$request = Illuminate\Http\Request::create('/api/lf-itse/licencias-funcionamiento/inactivar', 'POST', ['licencia_id' => 1, 'motivo' => 'test']);
$response = $app->handle($request);
echo $response->getContent();
