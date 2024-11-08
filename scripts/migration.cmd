@ECHO off
cd "%~dp0/.."
docker exec php-apache /var/www/docker/migration.sh
pause