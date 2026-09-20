# Incidente: Stubs en 5 UseCases de Licencias

## Contexto
El 2026-09-20 se detectó que 5 UseCases de Licencias fueron implementados
como stubs funcionales. El "diff JSON vacío" reportado era un falso
positivo (ambos lados devolvían [] con tablas vacías).

## UseCases afectados
1. ModificarLicenciaUseCase — clonación simple
2. EliminarLicenciaUseCase — sin validación de dependientes
3. ListarEstadosLicenciaUseCase — `return []` explícito
4. RegistrarInactivacionUseCase — sin check de duplicados
5. RegistrarNotificacionUseCase — sin validación de fechas

## Deuda generada
- 5 endpoints con lógica de juguete.
- Coverage 100% de código sin valor.
- Diff JSON falso positivo.

## Plan de resolución
Implementar los 5 UseCases copiando la lógica de
`services/licencia_funcionamiento.py` línea por línea.

## Lección aprendida
El diff JSON debe ejecutarse CON DATOS REALES en las tablas, no con
tablas vacías.
