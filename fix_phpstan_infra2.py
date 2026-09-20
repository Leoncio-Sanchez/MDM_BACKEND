import re

with open('app/Modules/Personas/Infrastructure/Persistence/Models/PersonaModel.php', 'r') as f:
    c = f.read()
c = c.replace("HasMany<PersonaDocumentoModel, \\$this>", "HasMany")
c = c.replace("public function documentos(): HasMany", "/** @return HasMany<PersonaDocumentoModel, $this> */\n    public function documentos(): HasMany")
with open('app/Modules/Personas/Infrastructure/Persistence/Models/PersonaModel.php', 'w') as f:
    f.write(c)

with open('app/Modules/Personas/Infrastructure/Persistence/Models/PersonaDocumentoModel.php', 'r') as f:
    c = f.read()
c = c.replace("BelongsTo<PersonaModel, \\$this>", "BelongsTo")
c = c.replace("use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;", "use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;")
c = c.replace("public function persona(): BelongsTo", "/** @return BelongsTo<PersonaModel, $this> */\n    public function persona(): BelongsTo")
with open('app/Modules/Personas/Infrastructure/Persistence/Models/PersonaDocumentoModel.php', 'w') as f:
    f.write(c)

