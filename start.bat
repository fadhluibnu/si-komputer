@echo off
title Development Servers Starter - SQLite Edition

SET LARAVEL_PORT=8000

cls
echo.
echo =================================================
echo ==       MEMULAI SERVER PENGEMBANGAN           ==
echo =================================================
echo.

REM ### LANGKAH 1: DETEKSI ALAMAT IP LOKAL ###
echo [1/3] Mendeteksi Alamat IP Lokal...
set "HOST_IP=127.0.0.1"
for /f "tokens=2 delims=:" %%a in ('ipconfig ^| findstr /i "IPv4"') do (
    for /f "tokens=*" %%b in ("%%a") do set "HOST_IP=%%b"
)
echo      -> Server akan berjalan di: %HOST_IP%
echo.
pause
cls

REM Pindah ke direktori tempat skrip ini berada
cd /d %~dp0
cd sistem

REM ####################################################################
REM ### PENTING: Menambahkan path Node.js portabel ke PATH sementara ###
set "PATH=%CD%\..\bin\nodejs;%PATH%"
REM ####################################################################

REM ### LANGKAH 2: MULAI LARAVEL ARTISAN SERVER ###
echo [2/3] Memulai Laravel Server di http://%HOST_IP%:%LARAVEL_PORT%
START "Laravel Backend" ..\bin\php\php.exe artisan serve --host=%HOST_IP% --port=%LARAVEL_PORT%

REM ### LANGKAH 3: MULAI VITE DEV SERVER ###
echo [3/3] Memulai Vite Frontend Server...
rem Sekarang kita bisa memanggil npm secara langsung karena path sudah diatur
START "Vite Frontend" call npm run dev -- --host

cd ..

echo.
echo ==========================================================
echo               SEMUA SERVER TELAH DIMULAI!
echo ==========================================================
echo.
echo - Buka browser dan akses: http://%HOST_IP%:%LARAVEL_PORT%
echo - Dua jendela terminal telah dibuka. Biarkan tetap berjalan.
echo.
pause
