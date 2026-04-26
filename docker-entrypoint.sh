#!/bin/bash
set -e

PORT="${PORT:-80}"

# Update Apache to listen on Railway's assigned port
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/*:80/*:${PORT}/" /etc/apache2/sites-available/000-default.conf

exec apache2-foreground
