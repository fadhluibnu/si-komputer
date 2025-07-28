@echo off
title Portable Laravel Installer

:: =================================================================
::      KONFIGURASI PENGGUNA DATABASE (Ubah Jika Perlu)
:: =================================================================
SET DB_USER=root
SET DB_PASS=
:: =================================================================

cls
echo.
echo =================================================
echo ==       INSTALLER PROYEK LARAVEL PORTABEL     ==
echo =================================================
echo.
echo Skrip ini akan menginstal semua dependensi untuk
echo proyek di dalam folder 'sistem'.
echo Pastikan semua binaries ada di folder 'bin'.
echo.
pause
cls

REM Pindah ke direktori tempat skrip ini berada
cd /d %~dp0

REM ### LANGKAH 1: SETUP FILE .ENV ###
echo [1/7] Menyiapkan file .env...
if not exist "sistem\.env" (
    if exist "sistem\.env.example" (
        copy "sistem\.env.example" "sistem\.env" > nul
        echo      -> File .env berhasil dibuat di dalam folder 'sistem'.
    ) else (
        echo      -> KESALAHAN: File 'sistem\.env.example' tidak ditemukan.
        goto:error
    )
) else (
    echo      -> File .env sudah ada, langkah ini dilewati.
)
echo.

REM Pindah ke folder proyek untuk menjalankan sisa perintah
cd sistem

REM ### LANGKAH 2: INSTALL DEPENDENSI PHP (COMPOSER) ###
echo [2/7] Menginstall dependensi PHP (Composer)...
..\bin\php\php.exe ..\bin\composer.phar update --no-interaction --prefer-dist --optimize-autoloader
if %errorlevel% neq 0 (
    echo      -> KESALAHAN: Composer install gagal.
    goto:error
)
echo      -> Dependensi Composer berhasil diinstall.
echo.

REM ### LANGKAH 3: INSTALL DEPENDENSI NODE.JS (NPM) ###
echo [3/7] Menginstall dependensi Node.js (NPM)...
call ..\bin\nodejs\npm.cmd install
if %errorlevel% neq 0 (
    echo      -> KESALAHAN: npm install gagal.
    goto:error
)
echo      -> Dependensi NPM berhasil diinstall.
echo.
echo [4/7] Compiling aset frontend (npm run build)...
call ..\bin\nodejs\npm.cmd run build
if %errorlevel% neq 0 (
    echo      -> KESALAHAN: npm run build gagal.
    goto:error
)
echo      -> Aset frontend berhasil di-compile.
echo.

REM ### LANGKAH 5: GENERATE APP KEY & STORAGE LINK ###
echo [5/7] Menghasilkan App Key & Storage Link...
..\bin\php\php.exe artisan key:generate
..\bin\php\php.exe artisan storage:link
echo.

REM ### LANGKAH 6: MEMBUAT DATABASE ###
echo [6/7] Membaca dan membuat database...
setlocal
set "DB_NAME="
REM Cari DB_DATABASE di file .env yang ada di folder saat ini (sistem)
FOR /F "tokens=1,* delims==" %%i IN ('findstr /B "DB_DATABASE=" .env') DO (
    SET "DB_NAME=%%j"
)
if not defined DB_NAME (
    echo      -> KESALAHAN: Variabel DB_DATABASE tidak ditemukan di .env.
    endlocal
    goto:error
)
echo      -> Nama database ditemukan: %DB_NAME%
echo      -> Mencoba membuat database...
..\bin\mariadb\bin\mysql.exe -u %DB_USER% -p%DB_PASS% -e "CREATE DATABASE IF NOT EXISTS %DB_NAME% CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
if %errorlevel% neq 0 (
    echo      -> PERINGATAN: Gagal membuat database. Mungkin sudah ada atau kredensial salah.
) else (
    echo      -> Database '%DB_NAME%' berhasil dibuat atau sudah ada.
)
echo.

REM ### LANGKAH 7: MIGRASI & SEEDING DATABASE ###
echo [7/7] Menjalankan migrasi dan seeder...
..\bin\php\php.exe artisan migrate --force
if %errorlevel% neq 0 (
    echo      -> KESALAHAN: Migrasi database gagal.
    endlocal
    goto:error
)
..\bin\php\php.exe artisan db:seed --force
echo      -> Migrasi dan seeder berhasil dijalankan.
echo.

goto:success

:error
echo.
echo #####################################
echo #         INSTALASI GAGAL!          #
echo #####################################
echo Periksa pesan kesalahan di atas untuk menemukan masalah.
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