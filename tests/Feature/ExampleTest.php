<?php

declare(strict_types=1);

it('no muestra la página de bienvenida en la raíz', function () {
    $this->get('/')->assertNotFound()->assertDontSee('Laravel');
});

it('mantiene la ruta de salud', function () {
    $this->get('/up')->assertOk();
});
