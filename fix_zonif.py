with open('app/Modules/Zonificacion/Presentation/Controllers/ZonificacionController.php', 'r') as f:
    c = f.read()

import re

c = re.sub(
r"    public function store\(GuardarZonificacionRequest \$request\): JsonResponse \{\s*\$dto = new GuardarZonificacionDTO\(\s*codigo: \$request->codigo,\s*nombre: \$request->nombre,\s*estaActivo: \(bool\)\$request->esta_activo,\s*usuarioId: \$request->user\('api'\)->id\s*\);\s*\$entity = \$this->crear->execute\(\$dto\);\s*return new JsonResponse\(new ZonificacionResource\(\$entity\), 201\);\s*\}",
r"""    public function store(GuardarZonificacionRequest $request): JsonResponse {
        $user = $request->user('api');
        if (!$user) {
            return new JsonResponse(['message' => 'Unauthenticated'], 401);
        }

        $dto = new GuardarZonificacionDTO(
            codigo: $request->codigo, 
            nombre: $request->nombre, 
            estaActivo: (bool)$request->esta_activo,
            usuarioId: $user->id
        );
        $entity = $this->crear->execute($dto);
        return new JsonResponse(new ZonificacionResource($entity), 201);
    }""", c)

c = re.sub(
r"    public function update\(GuardarZonificacionRequest \$request, int \$id\): JsonResponse \{\s*\$dto = new GuardarZonificacionDTO\(\s*codigo: \$request->codigo,\s*nombre: \$request->nombre,\s*estaActivo: \(bool\)\$request->esta_activo,\s*usuarioId: \$request->user\('api'\)->id\s*\);\s*\$entity = \$this->actualizar->execute\(\$id, \$dto\);\s*return new JsonResponse\(new ZonificacionResource\(\$entity\)\);\s*\}",
r"""    public function update(GuardarZonificacionRequest $request, int $id): JsonResponse {
        $user = $request->user('api');
        if (!$user) {
            return new JsonResponse(['message' => 'Unauthenticated'], 401);
        }

        $dto = new GuardarZonificacionDTO(
            codigo: $request->codigo, 
            nombre: $request->nombre, 
            estaActivo: (bool)$request->esta_activo,
            usuarioId: $user->id
        );
        $entity = $this->actualizar->execute($id, $dto);
        return new JsonResponse(new ZonificacionResource($entity));
    }""", c)

with open('app/Modules/Zonificacion/Presentation/Controllers/ZonificacionController.php', 'w') as f:
    f.write(c)

