@echo off
setlocal enabledelayedexpansion
title Portable Laravel Installer - SQLite Edition v3

cls
echo.
echo =================================================
echo ==    INSTALLER PROYEK LARAVEL (SQLite Edition)  ==
echo =================================================
echo.
pause
cls

REM Pindah ke direktori tempat skrip ini berada
cd /d %~dp0

REM ### LANGKAH 1: SETUP .ENV ###
echo [1/6] Menyiapkan file .env...
if not exist "sistem\.env" (
    if exist "sistem\.env.example" (
        copy "sistem\.env.example" "sistem\.env" > nul
    ) else (
        echo      -> KESALAHAN: File 'sistem\.env.example' tidak ditemukan.
        goto :handle_error
    )
)
echo.

REM Pindah ke folder proyek untuk menjalankan sisa perintah
cd sistem

REM ### LANGKAH 2: VERIFIKASI PATH PHP ###
echo [2/6] Memverifikasi path PHP...
if not exist "..\bin\php\php.exe" (
    echo      -> KESALAHAN: File '..\bin\php\php.exe' tidak ditemukan.
    echo      Pastikan PHP sudah diekstrak dengan benar ke dalam folder 'bin\php'.
    goto :handle_error
)
echo      -> Path PHP ditemukan.
echo.


REM ### LANGKAH 4: INSTALL DEPENDENSI ###
echo [4/6] Menginstall dependensi...
..\bin\php\php.exe ..\bin\composer.phar install --no-progress --quiet || goto :handle_error
call ..\bin\nodejs\npm.cmd install --quiet > nul || goto :handle_error
call ..\bin\nodejs\npm.cmd run build --quiet > nul || goto :handle_error
echo      -> Dependensi berhasil diinstall.
echo.

REM ### LANGKAH 5: GENERATE KEY & BUAT DATABASE ###
echo [5/6] Menghasilkan Key dan Menyiapkan Database...
..\bin\php\php.exe artisan key:generate || goto :handle_error
..\bin\php\php.exe artisan storage:link || goto :handle_error

REM Membuat file database kosong jika belum ada
if not exist "database\database.sqlite" (
    if not exist "database" mkdir "database"
    type nul > "database\database.sqlite"
    echo      -> File 'database\database.sqlite' berhasil dibuat.
) else (
    echo      -> File database sudah ada.
)
echo.

REM ### LANGKAH 6: MIGRASI & SEEDING ###
echo [6/6] Menjalankan migrasi dan seeder...
..\bin\php\php.exe artisan migrate --force
if !errorlevel! neq 0 (
    echo      -> KESALAHAN: Migrasi gagal. Periksa path database di .env.
    goto :handle_error
)
..\bin\php\php.exe artisan db:seed --force
echo      -> Migrasi dan seeder berhasil dijalankan.
echo.

goto:success


:handle_error
echo.
echo #####################################
echo #         INSTALASI GAGAL!          #
echo #####################################
echo Periksa pesan kesalahan di atas.
cd ..
pause
exit /b 1

:success
endlocal
echo.
echo #####################################
echo #        INSTALASI BERHASIL!        #
echo #####################################
echo Proyek Anda siap digunakan. Jalankan '2-start.bat'.
echo.
cd ..
pause
