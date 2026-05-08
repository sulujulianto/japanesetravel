#!/bin/sh
set -eu

sh railway/init-app.sh --runtime-only

port="${PORT:-8080}"

sed -ri "s/^Listen [^ ]+$/Listen 0.0.0.0:${port}/" /etc/apache2/ports.conf
sed -ri "s!<VirtualHost \\*:[0-9]+>!<VirtualHost *:${port}>!" /etc/apache2/sites-available/000-default.conf

exec apache2-foreground
