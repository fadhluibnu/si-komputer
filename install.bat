@echo off
title Portable Laravel Installer

:: ... (Bagian konfigurasi DB_USER dan DB_PASS tetap sama) ...
SET DB_USER=root
SET DB_PASS=

cls
echo.
echo =================================================
echo ==       INSTALLER PROYEK LARAVEL PORTABEL     ==
echo =================================================
echo.
pause
cls

REM Pindah ke direktori tempat skrip ini berada
cd /d %~dp0

REM ... (Langkah 1 sampai 5 tetap sama persis) ...

REM ### LANGKAH 1: SETUP FILE .ENV ###
echo [1/8] Menyiapkan file .env...
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
echo [2/8] Menginstall dependensi PHP (Composer)...
..\bin\php\php.exe ..\bin\composer.phar install
if %errorlevel% neq 0 ( goto:error )
echo      -> Dependensi Composer berhasil diinstall.
echo.

REM ### LANGKAH 3: INSTALL DEPENDENSI NODE.JS (NPM) ###
echo [3/8] Menginstall dependensi Node.js (NPM)...
call ..\bin\nodejs\npm.cmd install
if %errorlevel% neq 0 ( goto:error )
echo      -> Dependensi NPM berhasil diinstall.
echo.

REM ### LANGKAH 4: COMPILE ASET FRONTEND ###
echo [4/8] Compiling aset frontend (npm run build)...
call ..\bin\nodejs\npm.cmd run build
if %errorlevel% neq 0 ( goto:error )
echo      -> Aset frontend berhasil di-compile.
echo.

REM ### LANGKAH 5: GENERATE APP KEY & STORAGE LINK ###
echo [5/8] Menghasilkan App Key & Storage Link...
..\bin\php\php.exe artisan key:generate
..\bin\php\php.exe artisan storage:link
echo.

REM ### LANGKAH 6: MEMBACA NAMA DATABASE ###
echo [6/8] Membaca nama database dari file .env...
setlocal
set "DB_NAME="
FOR /F "tokens=1,* delims==" %%i IN ('findstr /B "DB_DATABASE=" .env') DO ( SET "DB_NAME=%%j" )
if not defined DB_NAME (
    echo      -> KESALAHAN: Variabel DB_DATABASE tidak ditemukan di .env.
    endlocal
    goto:error
)
echo      -> Nama database ditemukan: %DB_NAME%
echo.

REM ==================================================
REM ### PERUBAHAN UTAMA DIMULAI DI SINI ###
REM ==================================================

REM ### LANGKAH 7: MENYALAKAN SERVER DB SEMENTARA & MEMBUAT DATABASE ###
echo [7/8] Menyalakan server database sementara untuk instalasi...
START "Temporary DB Server" ..\bin\mariadb\bin\mysqld.exe --console
echo      -> Menunggu server database siap...
timeout /t 10 /nobreak > nul

echo      -> Mencoba membuat database '%DB_NAME%'...
..\bin\mariadb\bin\mysql.exe -u %DB_USER% -p%DB_PASS% -e "CREATE DATABASE IF NOT EXISTS %DB_NAME% CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
if %errorlevel% neq 0 (
    echo      -> PERINGATAN: Gagal membuat database. Mungkin kredensial salah.
) else (
    echo      -> Database berhasil dibuat atau sudah ada.
)
echo.

REM ### LANGKAH 8: MIGRASI & SEEDING ###
echo [8/8] Menjalankan migrasi dan seeder...
..\bin\php\php.exe artisan migrate --force
if %errorlevel% neq 0 (
    echo      -> KESALAHAN: Migrasi database gagal.
    call :shutdown_db
    endlocal
    goto:error
)
..\bin\php\php.exe artisan db:seed --force
echo      -> Migrasi dan seeder berhasil dijalankan.
echo.

REM Matikan server database sementara setelah selesai
call :shutdown_db
goto:success


REM Fungsi untuk mematikan server DB
:shutdown_db
echo      -> Mematikan server database sementara...
..\bin\mariadb\bin\mysqladmin.exe -u %DB_USER% -p%DB_PASS% shutdown
timeout /t 3 /nobreak > nul
goto:eof


:error
echo.
echo #####################################
echo #         INSTALASI GAGAL!          #
echo #####################################
cd ..
pause
exit /b 1

:success
endlocal
echo.
echo #####################################
echo #        INSTALASI BERHASIL!        #
echo #####################################
echo Proyek Anda siap digunakan. Jalankan '2-start.bat' untuk memulai pengembangan.
echo.
cd ..
pause