#!/bin/sh

set -eu

application_port="${PORT:-8080}"

sed -ri "s/^Listen [0-9]+$/Listen ${application_port}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${application_port}>/" /etc/apache2/sites-available/*.conf

exec "$@"
