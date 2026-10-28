#!/usr/bin/env bash
# ==============================================================================
# Respaldo automatizado y rotación — Sistema LF-ITSE (Municipalidad Distrital de Morales)
#
# Corte 2 (equipo), integrado en main con estos agregados:
#   - Respalda también los ARCHIVOS ADJUNTOS (actas, fotos de inspección): son evidencia
#     ante OCI y no están en la base de datos.
#   - Funciona con PostgreSQL en Docker (DB_CONTAINER) o instalado en el servidor.
#   - Avisa por WEBHOOK_URL ante cualquier fallo, no solo en pg_dump.
#
# Uso (cron, todos los días a la 01:00):
#   0 1 * * * DB_CONTAINER=mdm_backend-pgsql-1 DB_NAME=laravel DB_USER=sail \
#             MEDIA_DIR=/ruta/a/MEDIA_DISK_ROOT /ruta/scripts/backup_pg_morales.sh
#
# Las copias quedan en el mismo servidor: sincronícelas fuera (rsync / rclone) a otro equipo.
# ==============================================================================

set -euo pipefail

# ── Configuración ────────────────────────────────────────────────────
DB_CONTAINER="${DB_CONTAINER:-}"          # contenedor Docker de PostgreSQL (vacío = pg_dump local)
DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-5432}"
DB_NAME="${DB_NAME:?Defina DB_NAME}"
DB_USER="${DB_USER:?Defina DB_USER}"
export PGPASSWORD="${DB_PASSWORD:-}"

MEDIA_DIR="${MEDIA_DIR:-}"                # carpeta de adjuntos (MEDIA_DISK_ROOT); vacío = no se respalda

BACKUP_ROOT="${BACKUP_ROOT:-/var/backups/postgres/muni_morales}"
DIR_DIARIOS="${BACKUP_ROOT}/diarios"
DIR_MENSUALES="${BACKUP_ROOT}/mensuales"
LOG_FILE="${BACKUP_ROOT}/backup.log"

WEBHOOK_URL="${WEBHOOK_URL:-}"
RETENCION_DIAS=30
RETENCION_MESES=12

TIMESTAMP="$(date +'%Y%m%d_%H%M%S')"
DIA_DEL_MES="$(date +'%d')"
BACKUP_BD="${DIR_DIARIOS}/muni_morales_${TIMESTAMP}.sql.gz"
BACKUP_ADJUNTOS="${DIR_DIARIOS}/muni_morales_adjuntos_${TIMESTAMP}.tar.gz"

mkdir -p "${DIR_DIARIOS}" "${DIR_MENSUALES}"

log() {
    local mensaje="[$(date +'%Y-%m-%d %H:%M:%S')] $1"
    echo "${mensaje}"
    echo "${mensaje}" >> "${LOG_FILE}"
}

enviar_alerta() {
    local error_msg="$1"
    log "ERROR CRITICO: ${error_msg}"
    if [ -n "${WEBHOOK_URL}" ]; then
        curl -s -X POST -H "Content-Type: application/json" \
            -d "{\"text\": \"[ALERTA BACKUP MUNI MORALES] ${error_msg//\"/\'}\"}" \
            "${WEBHOOK_URL}" || true
    fi
}

# Cualquier comando que falle (no solo pg_dump) dispara la alerta
trap 'enviar_alerta "Falló el respaldo en la línea ${LINENO}: ${BASH_COMMAND}"' ERR

volcar_bd() {
    if [ -n "${DB_CONTAINER}" ]; then
        docker exec "${DB_CONTAINER}" pg_dump -U "${DB_USER}" -d "${DB_NAME}" --format=plain --no-owner --no-acl
    else
        pg_dump -h "${DB_HOST}" -p "${DB_PORT}" -U "${DB_USER}" -d "${DB_NAME}" --format=plain --no-owner --no-acl
    fi
}

log "Iniciando respaldo de la base '${DB_NAME}'${DB_CONTAINER:+ (contenedor ${DB_CONTAINER})}..."

# 1. Base de datos, comprimida y verificada (si falla, no se deja un archivo incompleto que parezca válido)
if ! volcar_bd | gzip -9 > "${BACKUP_BD}"; then
    rm -f "${BACKUP_BD}"
    enviar_alerta "pg_dump falló: no se pudo respaldar la base '${DB_NAME}'."
    exit 1
fi
if ! gzip -t "${BACKUP_BD}"; then
    rm -f "${BACKUP_BD}"
    enviar_alerta "El respaldo de la base '${BACKUP_BD}' está corrupto."
    exit 1
fi
log "Base de datos respaldada: $(basename "${BACKUP_BD}") ($(du -h "${BACKUP_BD}" | cut -f1))"

# 2. Archivos adjuntos (actas, fotos), comprimidos y verificados
if [ -n "${MEDIA_DIR}" ]; then
    if [ ! -d "${MEDIA_DIR}" ]; then
        enviar_alerta "La carpeta de adjuntos '${MEDIA_DIR}' no existe."
        exit 1
    fi
    if ! tar -czf "${BACKUP_ADJUNTOS}" -C "${MEDIA_DIR}" .; then
        rm -f "${BACKUP_ADJUNTOS}"
        enviar_alerta "No se pudieron respaldar los adjuntos de '${MEDIA_DIR}'."
        exit 1
    fi
    if ! gzip -t "${BACKUP_ADJUNTOS}"; then
        rm -f "${BACKUP_ADJUNTOS}"
        enviar_alerta "El respaldo de adjuntos '${BACKUP_ADJUNTOS}' está corrupto."
        exit 1
    fi
    log "Adjuntos respaldados: $(basename "${BACKUP_ADJUNTOS}") ($(du -h "${BACKUP_ADJUNTOS}" | cut -f1))"
else
    log "AVISO: MEDIA_DIR no está definido; los archivos adjuntos NO se respaldaron."
fi

# 3. Copia mensual (primer día del mes)
if [ "${DIA_DEL_MES}" = "01" ]; then
    cp "${BACKUP_BD}" "${DIR_MENSUALES}/muni_morales_MENSUAL_${TIMESTAMP}.sql.gz"
    [ -f "${BACKUP_ADJUNTOS}" ] && cp "${BACKUP_ADJUNTOS}" "${DIR_MENSUALES}/muni_morales_adjuntos_MENSUAL_${TIMESTAMP}.tar.gz"
    log "Copia mensual archivada."
fi

# 4. Rotación: diarios > 30 días y mensuales > 12 meses
find "${DIR_DIARIOS}" -name "muni_morales_*" -type f -mtime +"${RETENCION_DIAS}" -exec rm -f {} +
find "${DIR_MENSUALES}" -name "muni_morales_*MENSUAL_*" -type f -mtime +$((RETENCION_MESES * 30)) -exec rm -f {} +
log "Rotación aplicada (diarios: ${RETENCION_DIAS} días, mensuales: ${RETENCION_MESES} meses)."

log "Respaldo finalizado con éxito."
exit 0
