@echo off
setlocal enabledelayedexpansion
title Portable Laravel Installer - SQLite Edition v6

cls
echo.
echo =================================================
echo ==    INSTALLER PROYEK LARAVEL (SQLite Edition)  ==
echo =================================================
echo.
cls

REM Pindah ke direktori tempat skrip ini berada
cd /d %~dp0

REM ### LANGKAH 1: SETUP .ENV ###
echo [1/5] Menyiapkan file .env...
if not exist "sistem\.env" (
    if exist "sistem\.env.example" (
        copy "sistem\.env.example" "sistem\.env" > nul
    ) else (
        echo      -> KESALAHAN: File 'sistem\.env.example' tidak ditemukan.
        goto :handle_error
    )
)
echo.

echo Mengatur path database absolut di file .env...
rem Dapatkan path absolut dari direktori saat ini.
set "ABSOLUTE_DB_PATH=%~dp0sistem\database\database.sqlite"
rem Ganti backslash (\) dengan forward slash (/) untuk format yang lebih universal.
set "ABSOLUTE_DB_PATH_FWDSLASH=%ABSOLUTE_DB_PATH:\=/%"

rem Gunakan PowerShell untuk mengganti baris DB_DATABASE, mengapitnya dengan tanda kutip.
set "PS_COMMAND=$content = (Get-Content -Path 'sistem\.env' -Raw) -replace 'DB_DATABASE=.*', 'DB_DATABASE=\"%ABSOLUTE_DB_PATH_FWDSLASH%\"'; [System.IO.File]::WriteAllText('sistem\.env', $content, [System.Text.Encoding]::UTF8)"
powershell -Command "%PS_COMMAND%"

if !errorlevel! neq 0 (
    echo      -> KESALAHAN: Gagal menulis ke file .env menggunakan PowerShell.
    goto :handle_error
)
echo      -> Path database berhasil diatur.
echo.

REM Pindah ke folder proyek untuk menjalankan sisa perintah
cd sistem

REM ####################################################################
REM ### PENTING: Menambahkan path Node.js portabel ke PATH sementara ###
set "PATH=%CD%\..\bin\nodejs;%PATH%"
REM ####################################################################

REM ### LANGKAH 3: INSTALL DEPENDENSI ###
echo [3/5] Menginstall dependensi...
..\bin\php\php.exe ..\bin\composer.phar install
if !errorlevel! neq 0 (
    echo      -> KESALAHAN: Gagal menginstall dependensi Composer.
    goto :handle_error
)

call npm install
if !errorlevel! neq 0 (
    echo      -> KESALAHAN: Gagal menginstall dependensi npm.
    goto :handle_error
)

call npm run build
if !errorlevel! neq 0 (
    echo      -> KESALAHAN: Gagal menjalankan npm run build.
    goto :handle_error
)

echo      -> Dependensi berhasil diinstall.
echo.

REM ### LANGKAH 4: GENERATE KEY & BUAT DATABASE ###
echo [4/5] Menghasilkan Key dan Menyiapkan Database...
..\bin\php\php.exe artisan key:generate
if !errorlevel! neq 0 (
    echo      -> KESALAHAN: Gagal generate application key.
    goto :handle_error
)

..\bin\php\php.exe artisan storage:link
if !errorlevel! neq 0 (
    echo      -> KESALAHAN: Gagal membuat storage link.
    goto :handle_error
)

if not exist "database\database.sqlite" (
    if not exist "database" mkdir "database"
    type nul > "database\database.sqlite"
    echo      -> File 'database\database.sqlite' berhasil dibuat.
) else (
    echo      -> File database sudah ada.
)
echo.

REM ### LANGKAH 5: RESET DATABASE DAN SEEDING ###
echo [5/5] Mereset database dan mengisi data awal...
rem Perintah migrate:fresh akan menghapus semua tabel dan menjalankan ulang migrasi.
rem Opsi --seed akan otomatis menjalankan seeder setelahnya.
..\bin\php\php.exe artisan migrate:fresh --seed --force
if !errorlevel! neq 0 (
    echo      -> KESALAHAN: Gagal mereset database atau menjalankan seeder.
    goto :handle_error
)
echo      -> Database berhasil direset dan diisi data awal.
echo.

goto :success

:handle_error
echo.
echo #####################################
echo #         INSTALASI GAGAL!          #
echo #####################################
echo Periksa pesan kesalahan di atas.
echo.
cd /d %~dp0
endlocal
exit /b 1

:success
echo.
echo #####################################
echo #        INSTALASI BERHASIL!        #
echo #####################################
echo Proyek Anda siap digunakan. Jalankan 'start.bat'.
echo.
cd /d %~dp0
endlocal
exit /b 0
