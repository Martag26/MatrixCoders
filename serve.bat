@echo off
REM ───────────────────────────────────────────────────────────────
REM  MatrixCoders — Servidor local para ngrok
REM  Arranca el servidor integrado de PHP en el puerto 8000
REM  usando router.php (sirve public/ en "/" y admin/ en "/admin")
REM ───────────────────────────────────────────────────────────────

cd /d "%~dp0"

set PHP_EXE=C:\xampp\php\php.exe
if not exist "%PHP_EXE%" set PHP_EXE=php

echo Iniciando MatrixCoders en http://localhost:8000
echo (Ctrl+C para parar)
echo.

"%PHP_EXE%" -S 0.0.0.0:8000 router.php
