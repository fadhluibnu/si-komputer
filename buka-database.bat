@echo off
title Buka Database SQLite (Portable)

REM Pindah ke direktori tempat skrip ini berada
cd /d %~dp0

:: Path ke aplikasi DB Browser portabel di dalam folder tools
SET DB_BROWSER_EXE="tools\db-browser\DB Browser for SQLite.exe"

cls
echo.
echo =================================================
echo ==       MEMBUKA DATABASE SQLITE               ==
echo =================================================
echo.

REM Cek apakah DB Browser ada di path yang ditentukan
if not exist %DB_BROWSER_EXE% (
    echo KESALAHAN: "DB Browser for SQLite.exe" tidak ditemukan di:
    echo %DB_BROWSER_EXE%
    echo.
    echo Pastikan Anda sudah mengekstrak DB Browser ke folder 'tools\db-browser'.
    pause
    exit /b 1
)

REM Cek apakah file database ada
if not exist "sistem\database\database.sqlite" (
    echo KESALAHAN: File 'sistem\database\database.sqlite' tidak ditemukan.
    echo.
    echo Harap jalankan '1-install.bat' terlebih dahulu.
    pause
    exit /b 1
)

echo -> Membuka 'database.sqlite' dengan DB Browser portabel...
START "DB Browser" %DB_BROWSER_EXE% "sistem\database\database.sqlite"

echo.
echo Aplikasi DB Browser telah dibuka.
pause
