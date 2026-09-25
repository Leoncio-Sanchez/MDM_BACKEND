# TOGAF ADM — Catálogo y Fichas Técnicas de Indicadores Clave (KPIs)

**Organización:** Municipalidad Distrital  
**Proyecto:** Plataforma Integral de Licencias y Gestión Territorial  
**Documento:** 11 — Indicadores  
**Fecha:** 2026-09-25  

---

## 1. Resumen de los 5 Indicadores Clave de Desempeño

| # | Nombre del Indicador | Situación AS-IS | Meta TO-BE | Frecuencia | Mecanismo de Medición | Responsable |
|---|----------------------|:---------------:|:----------:|:----------:|-----------------------|-------------|
| **KPI-01** | **Tiempo Promedio de Atención** | 45 minutos | **≤ 15 minutos** | Diaria / Semanal | Telemetría del sistema (duración entre inicio de solicitud y confirmación). | SGDEL / Jefatura de Atención |
| **KPI-02** | **Porcentaje de Trámites Digitales** | 10% | **≥ 70%** | Mensual | Conteo de expedientes generados vía Web vs. ventanilla presencial. | Oficina de TI / Transformación Digital |
| **KPI-03** | **Tasa de Información Duplicada** | 35% | **< 5%** | Mensual | Análisis de duplicidad en tablas `personas` y `documentos` mediante scripts de auditoría. | Administrador de Base de Datos |
| **KPI-04** | **Disponibilidad de la Plataforma** | 95.0% | **≥ 99.5%** | Continua (24/7) | Sondas de monitoreo HTTP externas y logs de uptime del servidor IIS. | Infraestructura y Soporte TI |
| **KPI-05** | **Volumen de Reclamos por Demora** | 120 / mes | **< 40 / mes** | Mensual | Libro de reclamaciones digital y mesa de partes presencial. | Gerencia Municipal / Defensoría Vecinal |

---

## 2. Fichas Técnicas Detalladas de Indicadores

### KPI-01: Tiempo Promedio de Atención en Registro de Trámite
- **Objetivo Estratégico:** Reducir la fricción y el tiempo invertido por el vecino o empresario en la formulación de su trámite.
- **Fórmula de Cálculo:**  
  $$\text{TPA} = \frac{\sum (\text{FechaHoraEnvio} - \text{FechaHoraInicio})}{\text{Total de Trámites Iniciados}}$$
- **Línea Base (AS-IS):** 45 minutos de interacción presencial promedio en ventanilla de atención.
- **Meta (TO-BE):** ≤ 15 minutos a través del portal de autogestión guiado.
- **Fuente de Datos:** Registros de auditoría del backend (`created_at` del expediente vs apertura de sesión).

### KPI-02: Porcentaje de Trámites 100% Digitales
- **Objetivo Estratégico:** Masificar la adopción del canal digital para descongestionar el palacio municipal y promover el gobierno digital.
- **Fórmula de Cálculo:**  
  $$\% \text{ Trámites Digitales} = \left( \frac{\text{Expedientes Iniciados por Portal Web}}{\text{Total de Expedientes Totales (Web + Ventanilla)}} \right) \times 100$$
- **Línea Base (AS-IS):** 10% (la inmensa mayoría acude presencialmente con carpetas físicas).
- **Meta (TO-BE):** ≥ 70% del volumen mensual al sexto mes de operación del portal ciudadano.
- **Fuente de Datos:** Tabla `expedientes` con campo de origen (`canal_ingreso = 'WEB'`).

### KPI-03: Tasa de Información Duplicada y Errónea
- **Objetivo Estratégico:** Garantizar la calidad, unicidad y consistencia de los datos del padrón de administrados y establecimientos.
- **Fórmula de Cálculo:**  
  $$\% \text{ Duplicidad} = \left( \frac{\text{Registros con DNI/RUC duplicado o datos redundantes reingresados}}{\text{Total de Registros de Personas/Empresas}} \right) \times 100$$
- **Línea Base (AS-IS):** 35% de datos reingresados manualmente en distintas áreas con discrepancias ortográficas.
- **Meta (TO-BE):** < 5% mediante validación automática y enriquecimiento de datos con PIDE (RENIEC / SUNAT).
- **Fuente de Datos:** Tabla `personas` y `persona_documentos` en PostgreSQL.

### KPI-04: Disponibilidad Operativa de la Plataforma (Uptime)
- **Objetivo Estratégico:** Asegurar un servicio ininterrumpido que cumpla con los plazos administrativos del TUO Ley 27444.
- **Fórmula de Cálculo:**  
  $$\text{Uptime \%} = \left( \frac{\text{Horas Totales del Mes} - \text{Horas de Caída o Indisponibilidad}}{\text{Horas Totales del Mes}} \right) \times 100$$
- **Línea Base (AS-IS):** 95.0% (interrupciones frecuentes de servidores locales y caídas de base de datos sin redundancia).
- **Meta (TO-BE):** ≥ 99.5% mensual (menos de 3.6 horas de indisponibilidad programada o no programada al mes).
- **Fuente de Datos:** Sondas de monitoreo externo y logs de eventos de IIS / Neon.

### KPI-05: Volumen Mensual de Reclamos por Demora y Mal Servicio
- **Objetivo Estratégico:** Incrementar la satisfacción del vecino y erradicar quejas fundadas por silencio administrativo negativo o demora.
- **Fórmula de Cálculo:** Conteo mensual simple de reclamos categorizados bajo "Demora en trámite de Licencia / ITSE".
- **Línea Base (AS-IS):** 120 reclamos mensuales registrados en el libro de reclamaciones.
- **Meta (TO-BE):** < 40 reclamos mensuales gracias a las alertas automáticas por correo y el cumplimiento de plazos del workflow.
- **Fuente de Datos:** Módulo de reclamaciones y reportes de Mesa de Partes.
