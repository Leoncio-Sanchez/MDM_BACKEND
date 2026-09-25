# TOGAF ADM — Fase Preliminar: Marco y Principios de Arquitectura

**Organización:** Municipalidad Distrital  
**Proyecto:** Plataforma Integral de Licencias y Gestión Territorial  
**Fase TOGAF:** Preliminar  
**Fecha:** 2026-09-25  

---

## 1. Alcance y Contexto Organizacional

La Fase Preliminar establece la capacidad arquitectural dentro de la entidad municipal, delimitando el alcance de gobernanza, los estándares aplicables y los compromisos adquiridos entre las distintas áreas organizacionales:
- **Órgano de Alta Dirección:** Alcaldía y Gerencia Municipal.
- **Órganos de Línea involucrados:**
  1. Subgerencia de Desarrollo Económico y Licencias (**SGDEL**).
  2. Subgerencia de Gestión del Riesgo de Desastres (**SGRD**).
  3. Oficina de Gestión del Riesgo de Desastres (**OGRD**).
- **Órgano de Soporte Tecnológico:** Oficina General de Tecnologías de la Información (**OGTI / IT**).

---

## 2. Catálogo Inicial de Stakeholders Clave (8 Mínimos)

| # | Stakeholder | Rol / Cargo | Nivel de Interés | Poder / Influencia | Expectativas Clave |
|---|-------------|-------------|------------------|---------------------|--------------------|
| **1** | **Alcalde** | Sponsor Estratégico / Autoridad Máxima | Alto | Alto | Reducción de quejas vecinales, fomento del comercio formal, transparencia y gobierno digital. |
| **2** | **Gerente Municipal** | Conductor Operativo General | Alto | Alto | Cumplimiento de metas del Plan Operativo Institucional (POI), reducción de costos y control presupuestario. |
| **3** | **Ciudadano / Empresa** | Usuario Final Externo (RUC 10 / 20) | Alto | Medio | Trámites ágiles, sin colas, sin pedidos duplicados de documentos y notificaciones oportunas. |
| **4** | **Inspector Municipal** | Evaluador de Campo (ITSE) | Medio | Medio | Herramientas móviles ágiles, registro fotográfico in-situ, sin doble digitación en papel. |
| **5** | **Especialista Técnico** | Revisor de Planos y Expedientes Complejos | Medio | Medio | Visualización ordenada de memorias descriptivas, planos y compatibilidad de zonificación. |
| **6** | **Administrador del Sistema** | Gestor de Accesos e Infraestructura TI | Alto | Alto | Estabilidad de servicios, facilidad de despliegue, seguridad de datos y monitoreo proactivo. |
| **7** | **Jefe de Licencias** | Responsable de Línea (SGDEL) | Alto | Alto | Emisión de licencias dentro de los plazos de ley (D.S. 002-2018-PCM), control de expedientes observados. |
| **8** | **Jefe de IT** | Líder Técnico de la Transformación Digital | Alto | Alto | Arquitectura mantenible, cumplimiento de estándares de interoperabilidad nacional (PIDE) y calidad de código. |

---

## 3. Principios de Arquitectura Empresarial (5 Mínimos)

Los principios rigen todas las decisiones de diseño, adquisición y desarrollo en la plataforma municipal.

### Principio 1: Interoperabilidad Nativa y Coexistencia con Sistemas Existentes
- **Enunciado:** La arquitectura debe comunicarse fluidamente con plataformas legacy existentes (módulo Django histórico) y servicios públicos del Estado a través de la Plataforma de Interoperabilidad del Estado Peruano (PIDE - RENIEC y SUNAT).
- **Racional:** Evita la reinvención de la rueda, garantiza una transición sin interrupciones del servicio y elimina la carga al ciudadano de presentar documentos que el Estado ya posee.
- **Implicación:** Todos los servicios deben exponer APIs RESTful estandarizadas con contratos JSON deterministas.

### Principio 2: Seguridad y Control de Acceso Basado en Roles (RBAC)
- **Enunciado:** Todo acceso a la información y ejecución de acciones en el sistema está restringido por el principio de mínimo privilegio asignado al rol del usuario autenticado.
- **Racional:** La información comercial, planos de edificaciones e identidades son confidenciales y están sujetas a la Ley de Protección de Datos Personales (Ley N° 29733).
- **Implicación:** Uso obligatorio de tokens JWT con claims firmados, expiración corta, separación estricta entre roles (Ciudadano, Inspector, Especialista, Administrador) y validación en cada endpoint.

### Principio 3: Trazabilidad Integral y No Repudio (Auditoría Inmutable)
- **Enunciado:** Cada cambio de estado, observación, aprobación o rechazo de un expediente debe registrarse de manera inalterable con identidad del usuario, fecha/hora exacta y sustento legal/técnico.
- **Racional:** Es requisito legal en la gestión pública para fiscalizaciones de Contraloría General de la República y defensa ante recursos de apelación.
- **Implicación:** Implementación de tablas de historial (`workflow_licencia_historial`) que actúen como bitácoras append-only, prohibiendo mutaciones directas de datos históricos.

### Principio 4: Alta Disponibilidad y Resiliencia (≥ 99.5%)
- **Enunciado:** La plataforma de atención ciudadana y gestión municipal debe garantizar un uptime mensual no menor al 99.5% durante días hábiles.
- **Racional:** La indisponibilidad del servicio vulnera los plazos administrativos del TUO de la Ley N° 27444 (LPAG) y genera perjuicio al administrado.
- **Implicación:** Desacoplamiento de procesos en segundo plano (Jobs asíncronos para emails), base de datos en clúster cloud gestionado (PostgreSQL Neon) y monitoreo constante de salud HTTP.

### Principio 5: Escalabilidad Horizontal y Arquitectura Limpia
- **Enunciado:** El diseño debe permitir el incremento de usuarios concurrentes mediante la separación estricta de responsabilidades (MVC plano) y la ausencia de estado en la capa de cómputo (stateless).
- **Racional:** Los picos de demanda en campañas de formalización tributaria o vencimiento de plazos no deben degradar la experiencia de usuario.
- **Implicación:** Sesiones stateless gobernadas por JWT, almacenamiento de archivos en volúmenes compartidos o buckets S3-compatibles, y código auditado mediante análisis estático riguroso (PHPStan nivel 8).

---

## 4. Restricciones del Entorno Institucional

### 4.1 Restricciones Tecnológicas
- **Lenguaje y Framework:** PHP 8.5 en backend utilizando Laravel 11.
- **Base de Datos Relacional:** PostgreSQL 16 sobre infraestructura gestionada (AWS Neon / Servidor Municipal).
- **Patrón Arquitectural:** MVC Plano (Model - Controller - FormRequest - Resource - Service), eliminando la sobreingeniería hexagonal.

### 4.2 Restricciones de Infraestructura
- **Servidor Web Operativo:** Windows Server 2022 ejecutando IIS (Internet Information Services) con módulo FastCGI para PHP.
- **Red:** Operación mixta (DMZ para el portal público ciudadano y red interna municipal aislada para funcionarios e inspectores).

### 4.3 Restricciones Presupuestales y Operativas
- **Presupuesto Municipal Acotado:** Priorización de herramientas Open Source o capas gratuitas/empresariales ya contratadas (sin adquisición de licencias comerciales prohibitivas).
- **Modelo de Pagos:** El sistema informático **NO procesa pagos en línea ni valida bauchers bancarios** en el aplicativo core (la verificación del derecho de trámite se realiza en tesorería/caja física o canales bancarios institucionales previos).
