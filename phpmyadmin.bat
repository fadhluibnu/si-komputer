@echo off
title phpMyAdmin Server

:: Pindah ke direktori tempat skrip ini berada
cd /d %~dp0

echo =================================================
echo ==       PHP MYADMIN SERVER                    ==
echo =================================================
echo.
echo Server untuk phpMyAdmin akan berjalan di:
echo http://127.0.0.1:8001
echo.
echo Tekan CTRL+C di jendela ini untuk menghentikan server.
echo.

REM Menjalankan server PHP internal yang menunjuk ke folder phpmyadmin
bin\php\php.exe -S 127.0.0.1:8001 -t tools\phpmyadmin