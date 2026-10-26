#!/usr/bin/env bash
#
# Sauvegarde quotidienne de SignalGabon : base MariaDB + preuves.
# L'archive est chiffrée avec GPG, car elle contient des signalements de
# corruption et l'identité de leurs auteurs lorsqu'ils en ont laissé une.
#
#   ./sauvegarde.sh [repertoire_de_destination]
#
# À mettre en cron :
#   15 2 * * * /opt/signal-gabon/deploy/sauvegarde.sh >> /var/log/signalgabon-sauvegarde.log 2>&1

set -euo pipefail

ICI="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
RACINE="$(dirname "$ICI")"
ENV_FILE="$RACINE/backend/.env"
DESTINATION="${1:-/var/sauvegardes/signalgabon}"

if [[ ! -f "$ENV_FILE" ]]; then
  echo "Erreur : $ENV_FILE introuvable." >&2
  exit 1
fi

lire_env() {
  # Lit une variable du .env sans exécuter le fichier.
  sed -n "s/^$1=//p" "$ENV_FILE" | tail -n1 | tr -d '"'"'"'\r'
}

DB_DATABASE="$(lire_env DB_DATABASE)"
DB_ROOT_PASSWORD="$(lire_env DB_ROOT_PASSWORD)"
GPG_DESTINATAIRE="$(lire_env SAUVEGARDE_GPG_DESTINATAIRE)"
RETENTION="$(lire_env SAUVEGARDE_RETENTION_JOURS)"
RETENTION="${RETENTION:-30}"

if [[ -z "$GPG_DESTINATAIRE" ]]; then
  echo "Erreur : SAUVEGARDE_GPG_DESTINATAIRE n'est pas défini dans $ENV_FILE." >&2
  echo "Une sauvegarde de signalements ne doit pas être écrite en clair." >&2
  exit 1
fi

HORODATAGE="$(date +%Y-%m-%d_%H%M)"
TRAVAIL="$(mktemp -d)"
trap 'rm -rf "$TRAVAIL"' EXIT

mkdir -p "$DESTINATION"
cd "$ICI"

echo "[$(date +%H:%M:%S)] Export de la base $DB_DATABASE…"
docker compose --env-file "$ENV_FILE" exec -T -e MYSQL_PWD="$DB_ROOT_PASSWORD" db \
  mariadb-dump --user=root --single-transaction --quick \
               --routines --events "$DB_DATABASE" > "$TRAVAIL/base.sql"

echo "[$(date +%H:%M:%S)] Copie des preuves…"
docker compose --env-file "$ENV_FILE" cp app:/var/www/html/storage/app/private/preuves "$TRAVAIL/preuves" 2>/dev/null \
  || mkdir -p "$TRAVAIL/preuves"

echo "[$(date +%H:%M:%S)] Chiffrement…"
ARCHIVE="$DESTINATION/signalgabon_$HORODATAGE.tar.gz.gpg"
tar -czf - -C "$TRAVAIL" base.sql preuves \
  | gpg --batch --yes --encrypt --recipient "$GPG_DESTINATAIRE" --output "$ARCHIVE"

chmod 600 "$ARCHIVE"
echo "[$(date +%H:%M:%S)] Archive : $ARCHIVE ($(du -h "$ARCHIVE" | cut -f1))"

echo "[$(date +%H:%M:%S)] Suppression des archives de plus de $RETENTION jours…"
find "$DESTINATION" -name 'signalgabon_*.tar.gz.gpg' -mtime "+$RETENTION" -delete

echo "[$(date +%H:%M:%S)] Terminé."
