<?php

declare(strict_types=1);

namespace App\Modules\Usuarios\Application\UseCases;

use App\Modules\Usuarios\Domain\Contracts\UsuarioRepositoryInterface;

final readonly class ConstruirMenuUsuarioUseCase
{
    public function __construct(
        private UsuarioRepositoryInterface $usuarioRepository,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function execute(int $usuarioId): array
    {
        $opciones = $this->usuarioRepository->obtenerOpcionesUsuario($usuarioId);

        $tieneCatalogo = (
            $opciones['expedientes']
            || $opciones['licencias']
            || $opciones['itse']
            || $opciones['admin']
        );

        $menus = [];

        // 1. Dashboard (siempre visible)
        $menus[] = [
            'id' => 'dashboard',
            'label' => 'Dashboard',
            'url' => '/dashboard',
            'submenues' => [],
        ];

        // 2. Expedientes
        if ($opciones['expedientes']) {
            $menus[] = [
                'id' => 'expedientes',
                'label' => 'Expedientes',
                'url' => '/expedientes',
                'submenues' => [],
            ];
        }

        // 3. Licencias de funcionamiento
        if ($opciones['licencias']) {
            $menus[] = [
                'id' => 'licencias-funcionamiento',
                'label' => 'Licencias de funcionamiento',
                'url' => '/licencias-funcionamiento',
                'submenues' => [],
            ];
        }

        // 4. Certificados ITSE
        if ($opciones['itse']) {
            $menus[] = [
                'id' => 'certificados-itse',
                'label' => 'Certificados ITSE',
                'url' => '/certificados-itse',
                'submenues' => [],
            ];
        }

        // 5. Reportes (siempre visible con sus submenús)
        $menus[] = [
            'id' => 'reportes',
            'label' => 'Reportes',
            'url' => '/reportes',
            'submenues' => [
                [
                    'id' => 'reportes-expedientes',
                    'label' => 'Expedientes',
                    'url' => '/reportes/expedientes',
                    'submenues' => [],
                ],
                [
                    'id' => 'reportes-licencias-funcionamiento',
                    'label' => 'Licencias de funcionamiento',
                    'url' => '/reportes/licencias-funcionamiento',
                    'submenues' => [],
                ],
                [
                    'id' => 'reportes-certificados-itse',
                    'label' => 'Certificados ITSE',
                    'url' => '/reportes/certificados-itse',
                    'submenues' => [],
                ],
            ],
        ];

        // 6. Catálogos
        if ($tieneCatalogo) {
            $menus[] = [
                'id' => 'catalogos',
                'label' => 'Catálogos',
                'url' => '/catalogos',
                'submenues' => [
                    [
                        'id' => 'catalogos-personas',
                        'label' => 'Personas',
                        'url' => '/catalogos/personas',
                        'submenues' => [],
                    ],
                    [
                        'id' => 'catalogos-giros',
                        'label' => 'Giros',
                        'url' => '/catalogos/giros',
                        'submenues' => [],
                    ],
                    [
                        'id' => 'catalogos-zonificaciones',
                        'label' => 'Zonificaciones',
                        'url' => '/catalogos/zonificaciones',
                        'submenues' => [],
                    ],
                    [
                        'id' => 'catalogos-tipos-procedimiento-tupa',
                        'label' => 'Tipo de procedimiento TUPA',
                        'url' => '/catalogos/tipos-procedimiento-tupa',
                        'submenues' => [],
                    ],
                ],
            ];
        }

        // 7. Usuarios
        if ($opciones['admin']) {
            $menus[] = [
                'id' => 'usuarios',
                'label' => 'Usuarios',
                'url' => '/usuarios',
                'submenues' => [],
            ];
        }

        return $menus;
    }
}
