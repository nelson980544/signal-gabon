#!/usr/bin/env bash
#
# Restauration d'une sauvegarde SignalGabon.
#
#   ./restaurer.sh /var/sauvegardes/signalgabon/signalgabon_2026-10-22_0215.tar.gz.gpg
#
# ÉCRASE la base et les preuves actuelles. Une confirmation est demandée.
# À tester au moins une fois après l'installation : une sauvegarde jamais
# restaurée n'est pas une sauvegarde.

set -euo pipefail

ICI="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
RACINE="$(dirname "$ICI")"
ENV_FILE="$RACINE/backend/.env"
ARCHIVE="${1:-}"

if [[ -z "$ARCHIVE" || ! -f "$ARCHIVE" ]]; then
  echo "Usage : $0 <archive.tar.gz.gpg>" >&2
  exit 1
fi

lire_env() {
  sed -n "s/^$1=//p" "$ENV_FILE" | tail -n1 | tr -d '"'"'"'\r'
}

DB_DATABASE="$(lire_env DB_DATABASE)"
DB_ROOT_PASSWORD="$(lire_env DB_ROOT_PASSWORD)"

echo "Cette opération remplace la base « $DB_DATABASE » et toutes les preuves"
echo "par le contenu de $(basename "$ARCHIVE")."
read -r -p "Taper RESTAURER pour confirmer : " reponse
if [[ "$reponse" != "RESTAURER" ]]; then
  echo "Annulé."
  exit 1
fi

TRAVAIL="$(mktemp -d)"
trap 'rm -rf "$TRAVAIL"' EXIT

echo "Déchiffrement…"
gpg --batch --quiet --decrypt "$ARCHIVE" | tar -xzf - -C "$TRAVAIL"

cd "$ICI"

echo "Restauration de la base…"
docker compose --env-file "$ENV_FILE" exec -T -e MYSQL_PWD="$DB_ROOT_PASSWORD" db \
  mariadb --user=root "$DB_DATABASE" < "$TRAVAIL/base.sql"

echo "Restauration des preuves…"
docker compose --env-file "$ENV_FILE" exec -T app sh -c 'rm -rf /var/www/html/storage/app/private/preuves/*'
docker compose --env-file "$ENV_FILE" cp "$TRAVAIL/preuves/." app:/var/www/html/storage/app/private/preuves/
docker compose --env-file "$ENV_FILE" exec -T -u root app \
  chown -R www-data:www-data /var/www/html/storage/app/private/preuves

echo "Terminé. Vérifier l'application avant de reprendre l'exploitation."
