#!/bin/sh

set -eu

mkdir -p \
    /tmp/apache2/run \
    /tmp/apache2/log \
    /tmp/apache2/lock \
    /tmp/laravel/views

chown -R www-data:www-data /tmp/apache2 /tmp/laravel

ln -sf /proc/self/fd/2 /tmp/apache2/log/error.log
ln -sf /proc/self/fd/1 /tmp/apache2/log/access.log
ln -sf /proc/self/fd/1 /tmp/apache2/log/other_vhosts_access.log

if [ -n "${MYSQL_SSL_CA_PEM:-}" ]; then
    umask 077
    printf '%s\n' "$MYSQL_SSL_CA_PEM" > /tmp/mysql-ca.pem
    chown www-data:www-data /tmp/mysql-ca.pem
fi

exec "$@"
