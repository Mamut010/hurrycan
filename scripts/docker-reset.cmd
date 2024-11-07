@ECHO off
cd "%~dp0/.."
docker compose down --volumes
docker compose up --watch --build