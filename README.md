# Backend PHP (Laravel 11) - Proyecto Municipal LF ITSE

## Requisitos
- Docker y Docker Compose
- NO requiere PHP local (usamos Laravel Sail)

## Instalación
```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate --seed
```

## Comandos de Calidad
```bash
./vendor/bin/sail pint
./vendor/bin/sail phpstan analyse
./vendor/bin/sail pest
```

## Estructura Modular
El proyecto utiliza módulos independientes en `app/Modules/`.
