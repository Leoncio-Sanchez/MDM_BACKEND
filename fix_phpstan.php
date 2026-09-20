<?php
function r($f, $s, $r) {
    file_put_contents($f, str_replace($s, $r, file_get_contents($f)));
}

r('app/Modules/NivelRiesgo/Application/UseCases/ListarNivelRiesgoUseCase.php', 'public function execute(): array {', "/**\n     * @return \App\Modules\NivelRiesgo\Domain\Entities\NivelRiesgo[]\n     */\n    public function execute(): array {");
r('app/Modules/NivelRiesgo/Domain/Contracts/NivelRiesgoRepositoryInterface.php', 'public function obtenerTodos(): array;', "/**\n     * @return NivelRiesgo[]\n     */\n    public function obtenerTodos(): array;");
r('app/Modules/NivelRiesgo/Infrastructure/Persistence/Repositories/EloquentNivelRiesgoRepository.php', 'public function obtenerTodos(): array {', "/**\n     * @return NivelRiesgo[]\n     */\n    public function obtenerTodos(): array {");
r('app/Modules/NivelRiesgo/Presentation/Requests/GuardarNivelRiesgoRequest.php', 'public function rules(): array {', "/**\n     * @return array<string, mixed>\n     */\n    public function rules(): array {");
r('app/Modules/NivelRiesgo/Presentation/Resources/NivelRiesgoResource.php', 'public function toArray(Request $request): array {', "/**\n     * @return array<string, mixed>\n     */\n    public function toArray(Request \$request): array {");
r('app/Modules/NivelRiesgo/Presentation/Resources/NivelRiesgoResource.php', "class NivelRiesgoResource extends JsonResource {\n", "/**\n * @property-read \App\Modules\NivelRiesgo\Domain\Entities\NivelRiesgo \$resource\n */\nclass NivelRiesgoResource extends JsonResource {\n");
r('app/Modules/NivelRiesgo/Presentation/Resources/NivelRiesgoResource.php', "\$this->id", "\$this->resource->id");
r('app/Modules/NivelRiesgo/Presentation/Resources/NivelRiesgoResource.php', "\$this->codigo", "\$this->resource->codigo");
r('app/Modules/NivelRiesgo/Presentation/Resources/NivelRiesgoResource.php', "\$this->nombre", "\$this->resource->nombre");
r('app/Modules/NivelRiesgo/Presentation/Resources/NivelRiesgoResource.php', "\$this->estaActivo", "\$this->resource->estaActivo");
