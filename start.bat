@echo off
title Development Servers Starter v2

:: =================================================================
::      KONFIGURASI SERVER (Ubah Port Jika Perlu)
:: =================================================================
SET LARAVEL_PORT=2025
:: =================================================================

cls
echo.
echo =================================================
echo ==       MEMULAI SERVER PENGEMBANGAN           ==
echo =================================================
echo.
echo [PENTING] Server akan berjalan dan bisa diakses dari jaringan.
echo.

REM Pindah ke direktori tempat skrip ini berada
cd /d %~dp0

REM Pindah ke folder proyek Laravel
cd sistem

REM ####################################################################
REM ### PENTING: Menambahkan path Node.js portabel ke PATH sementara ###
set "PATH=%CD%\..\bin\nodejs;%PATH%"
REM ####################################################################

REM ### LANGKAH 1: MULAI LARAVEL ARTISAN SERVER ###
echo [1/2] Memulai Laravel Server di SEMUA alamat IP (0.0.0.0) pada port %LARAVEL_PORT%...
START "Laravel Backend" ..\bin\php\php.exe artisan serve --host=0.0.0.0 --port=%LARAVEL_PORT%

REM ### LANGKAH 2: MULAI VITE DEV SERVER ###
echo [2/2] Memulai Vite Frontend Server...
rem Sekarang kita bisa memanggil npm secara langsung karena path sudah diatur
START "Vite Frontend" call npm run dev -- --host

cd ..

echo.
echo ==========================================================
echo               SEMUA SERVER TELAH DIMULAI!
echo ==========================================================
echo.
echo Untuk mengakses dari laptop ini, gunakan: http://localhost:%LARAVEL_PORT%
echo.
echo Untuk diakses orang lain, gunakan alamat IP Anda di jaringan ini.
echo.
pause
