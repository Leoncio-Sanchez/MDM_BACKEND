<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class DocumentacionApiTest extends TestCase
{
    public function test_swagger_no_se_sirve_fuera_de_local(): void
    {
        // El entorno de tests no es "local": se comporta como producción
        $this->get('/api/documentation')->assertNotFound();
        $this->get('/docs')->assertNotFound();
    }

    public function test_swagger_se_puede_habilitar_explicitamente(): void
    {
        config(['l5-swagger.defaults.publico' => true]);

        $this->get('/api/documentation')->assertOk();
    }
}
