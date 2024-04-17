#!/bin/sh
# green4T 2024
# Gabriel Pascoal - gabriel.pascoal@green4t.com 

set -x

log() {
  echo "$(date +"%Y-%m-%d %H:%M:%S") - $*"
}

glpi_install() {
  log "INFO: Installing DB GLPI"
  php ${GLPI_HOME}/bin/console glpi:database:install \
    --db-host ${DB_HOST} \
    --db-port ${DB_PORT} \
    --db-user ${DB_USER} \
    --db-name ${DB_NAME} \
    --db-password ${DB_PASSWORD} \
    -L pt_BR \
    --no-interaction \
    --reconfigure \
    --force -vvv
}

if [ ! -s /etc/glpi/config_db.php ]
then
  log "INFO: Installing GLPI"
  glpi_install
fi
/usr/local/bin/start.sh