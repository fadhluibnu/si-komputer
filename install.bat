@echo off
title Portable Laravel Installer - SQLite Edition

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

REM ### LANGKAH 1: SETUP .ENV & DEPENDENSI ###
echo [1/4] Menyiapkan .env dan menginstall dependensi...

if not exist "sistem\.env" (
    if exist "sistem\.env.example" (
        copy "sistem\.env.example" "sistem\.env" > nul
    ) else (
        echo      -> KESALAHAN: File 'sistem\.env.example' tidak ditemukan.
        goto :handle_error
    )
)

cd sistem

..\bin\php\php.exe ..\bin\composer.phar install --no-progress --quiet || goto :handle_error
call ..\bin\nodejs\npm.cmd install --quiet > nul || goto :handle_error
call ..\bin\nodejs\npm.cmd run build --quiet > nul || goto :handle_error

echo      -> Dependensi berhasil diinstall.
echo.

REM ### LANGKAH 2: GENERATE KEY & STORAGE LINK ###
echo [2/4] Menghasilkan App Key & Storage Link...
..\bin\php\php.exe artisan key:generate || goto :handle_error
..\bin\php\php.exe artisan storage:link || goto :handle_error
echo.

REM ### LANGKAH 3: MEMBUAT FILE DATABASE SQLITE ###
echo [3/4] Menyiapkan file database SQLite...
REM Cek apakah file .env sudah dikonfigurasi untuk sqlite
findstr /C:"DB_CONNECTION=sqlite" .env >nul
if %errorlevel% neq 0 (
    echo      -> KESALAHAN: Harap ubah DB_CONNECTION menjadi 'sqlite' di file .env Anda.
    goto :handle_error
)

REM Membuat file database kosong jika belum ada
if not exist "database\database.sqlite" (
    if not exist "database" mkdir "database"
    type nul > "database\database.sqlite"
    echo      -> File 'database\database.sqlite' berhasil dibuat.
) else (
    echo      -> File database sudah ada.
)
echo.

REM ### LANGKAH 4: MIGRASI & SEEDING ###
echo [4/4] Menjalankan migrasi dan seeder...
..\bin\php\php.exe artisan migrate --force
if %errorlevel% neq 0 (
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
echo.
echo #####################################
echo #        INSTALASI BERHASIL!        #
echo #####################################
echo Proyek Anda siap digunakan. Jalankan '2-start.bat'.
echo.
cd ..
pause