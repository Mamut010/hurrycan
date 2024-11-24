@ECHO off
cd "%~dp0/.."
docker exec php-server /var/www/docker/migration.sh
pause