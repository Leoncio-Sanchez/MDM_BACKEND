<?php
$file = 'app/Modules/Zonificacion/Presentation/Controllers/ZonificacionController.php';
$content = file_get_contents($file);

$search_store = 'public function store(GuardarZonificacionRequest $request): JsonResponse {
        $dto = new GuardarZonificacionDTO(
            codigo: $request->codigo, 
            nombre: $request->nombre, 
            estaActivo: (bool)$request->esta_activo,
            usuarioId: $request->user(\'api\')->id ?? 1
        );';

$replace_store = 'public function store(GuardarZonificacionRequest $request): JsonResponse {
        $user = $request->user(\'api\');
        if (!$user) {
            return new JsonResponse([\'message\' => \'Unauthenticated\'], 401);
        }

        $dto = new GuardarZonificacionDTO(
            codigo: $request->codigo, 
            nombre: $request->nombre, 
            estaActivo: (bool)$request->esta_activo,
            usuarioId: $user->id
        );';

$search_update = 'public function update(GuardarZonificacionRequest $request, int $id): JsonResponse {
        $dto = new GuardarZonificacionDTO(
            codigo: $request->codigo, 
            nombre: $request->nombre, 
            estaActivo: (bool)$request->esta_activo,
            usuarioId: $request->user(\'api\')->id ?? 1
        );';

$replace_update = 'public function update(GuardarZonificacionRequest $request, int $id): JsonResponse {
        $user = $request->user(\'api\');
        if (!$user) {
            return new JsonResponse([\'message\' => \'Unauthenticated\'], 401);
        }

        $dto = new GuardarZonificacionDTO(
            codigo: $request->codigo, 
            nombre: $request->nombre, 
            estaActivo: (bool)$request->esta_activo,
            usuarioId: $user->id
        );';

$content = str_replace($search_store, $replace_store, $content);
$content = str_replace($search_update, $replace_update, $content);

file_put_contents($file, $content);
