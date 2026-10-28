#!/usr/bin/env bash
# ==============================================================================
# Script de Respaldo Automatizado y Rotación de Base de Datos PostgreSQL
# Proyecto: Sistema LF-ITSE — Municipalidad Distrital de Morales
# ==============================================================================

set -euo pipefail

# Configuración de Entorno
DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-5432}"
DB_NAME="${DB_NAME:-muni_db}"
DB_USER="${DB_USER:-muni_user}"
export PGPASSWORD="${DB_PASSWORD:-secret}"

BACKUP_ROOT="${BACKUP_ROOT:-/var/backups/postgres/muni_morales}"
DIR_DIARIOS="${BACKUP_ROOT}/diarios"
DIR_MENSUALES="${BACKUP_ROOT}/mensuales"
LOG_FILE="${BACKUP_ROOT}/backup.log"

# Configuración de Alertas
WEBHOOK_URL="${WEBHOOK_URL:-}"
RETENCION_DIAS=30
RETENCION_MESES=12

TIMESTAMP="$(date +'%Y%m%d_%H%M%S')"
DIA_DEL_MES="$(date +'%d')"
BACKUP_FILENAME="muni_morales_${TIMESTAMP}.sql.gz"
BACKUP_PATH="${DIR_DIARIOS}/${BACKUP_FILENAME}"

# Crear directorios si no existen
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
            -d "{\"text\": \"🚨 [ALERTA BACKUP MUNI MORALES] Falló el volcado de BD: ${error_msg}\"}" \
            "${WEBHOOK_URL}" || true
    fi
}

log "Iniciando respaldo de base de datos '${DB_NAME}' en '${DB_HOST}:${DB_PORT}'..."

# 1. Ejecutar pg_dump con compresión gzip en tubería
if ! pg_dump -h "${DB_HOST}" -p "${DB_PORT}" -U "${DB_USER}" -d "${DB_NAME}" \
    --format=plain --no-owner --no-acl | gzip -9 > "${BACKUP_PATH}"; then
    enviar_alerta "pg_dump finalizó con código de error."
    exit 1
fi

# 2. Validación de integridad del archivo comprimido
if ! gzip -t "${BACKUP_PATH}"; then
    enviar_alerta "El archivo comprimido generado '${BACKUP_PATH}' está corrupto."
    rm -f "${BACKUP_PATH}"
    exit 1
fi

TAMANIO="$(du -h "${BACKUP_PATH}" | cut -f1)"
log "Respaldo diario generado exitosamente: ${BACKUP_FILENAME} (${TAMANIO})"

# 3. Copia mensual (si es el primer día del mes)
if [ "${DIA_DEL_MES}" = "01" ]; then
    MENSUAL_FILENAME="muni_morales_MENSUAL_${TIMESTAMP}.sql.gz"
    cp "${BACKUP_PATH}" "${DIR_MENSUALES}/${MENSUAL_FILENAME}"
    log "Copia mensual archivada: ${MENSUAL_FILENAME}"
fi

# 4. Política de rotación: depuración de copias diarias > 30 días
log "Aplicando rotación: eliminando diarios con antigüedad superior a ${RETENCION_DIAS} días..."
find "${DIR_DIARIOS}" -name "muni_morales_*.sql.gz" -type f -mtime +"${RETENCION_DIAS}" -exec rm -f {} +

# 5. Política de rotación: depuración de copias mensuales > 365 días (12 meses)
log "Aplicando rotación: eliminando mensuales con antigüedad superior a $((RETENCION_MESES * 30)) días..."
find "${DIR_MENSUALES}" -name "muni_morales_MENSUAL_*.sql.gz" -type f -mtime +$((RETENCION_MESES * 30)) -exec rm -f {} +

log "Proceso de respaldo y rotación finalizado con éxito."
exit 0
