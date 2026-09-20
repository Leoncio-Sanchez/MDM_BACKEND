with open('app/Modules/Zonificacion/Presentation/Controllers/ZonificacionController.php', 'r') as f:
    lines = f.readlines()

out = []
for i, l in enumerate(lines):
    if "public function store(GuardarZonificacionRequest $request): JsonResponse" in l:
        out.append(l)
        out.append("    {\n")
        out.append("        $user = $request->user('api');\n")
        out.append("        if (!$user) {\n")
        out.append("            return new JsonResponse(['message' => 'Unauthenticated'], 401);\n")
        out.append("        }\n")
        continue
    if "public function update(GuardarZonificacionRequest $request, int $id): JsonResponse" in l:
        out.append(l)
        out.append("    {\n")
        out.append("        $user = $request->user('api');\n")
        out.append("        if (!$user) {\n")
        out.append("            return new JsonResponse(['message' => 'Unauthenticated'], 401);\n")
        out.append("        }\n")
        continue
    if "usuarioId: $request->user('api')->id" in l:
        out.append("            usuarioId: $user->id\n")
        continue
    
    if l.strip() == "{" and ("public function store" in lines[i-1] or "public function update" in lines[i-1]):
        continue # skip the brace we already added

    out.append(l)

with open('app/Modules/Zonificacion/Presentation/Controllers/ZonificacionController.php', 'w') as f:
    f.writelines(out)

