@echo off
title Dependency Downloader (Node.js Only)

:: =================================================================
::      KONFIGURASI VERSI NODE.JS
:: =================================================================
SET NODE_VERSION=20.15.1
SET NODE_DOWNLOAD_URL=https://nodejs.org/dist/v%NODE_VERSION%/node-v%NODE_VERSION%-win-x64.zip
:: =================================================================

cls
echo.
echo =================================================
echo ==       PENGUNDUH DEPENDENSI (HANYA NODE.JS)    ==
echo =================================================
echo.
echo Skrip ini akan mengunduh Node.js jika belum ada.
echo Pastikan Anda memiliki koneksi internet.
echo.
pause
cls

REM Pindah ke direktori tempat skrip ini berada
cd /d %~dp0

REM Buat folder bin jika belum ada
if not exist "bin" mkdir "bin"

REM --- PROSES UNTUK NODE.JS ---
echo [1/1] Memeriksa instalasi Node.js...
if exist "bin\nodejs" (
    echo      -> Folder 'bin\nodejs' sudah ada. Proses unduh dilewati.
) else (
    echo      -> Folder 'bin\nodejs' tidak ditemukan. Memulai proses unduh...
    echo      -> Mengunduh Node.js v%NODE_VERSION%...
    curl -L "%NODE_DOWNLOAD_URL%" -o "node_temp.zip"
    if %errorlevel% neq 0 (
        echo      -> KESALAHAN: Gagal mengunduh Node.js. Periksa koneksi internet.
        goto :handle_error
    )

    echo      -> Mengekstrak file Node.js...
    mkdir "bin\nodejs"
    tar -xf node_temp.zip -C "bin\nodejs" --strip-components=1
    if %errorlevel% neq 0 (
        echo      -> KESALAHAN: Gagal mengekstrak Node.js.
        goto :handle_error
    )
    
    echo      -> Membersihkan file sementara...
    del node_temp.zip
    echo      -> Node.js berhasil diinstal.
)
echo.

goto:success


:handle_error
echo.
echo #####################################
echo #   PROSES DOWNLOAD GAGAL!          #
echo #####################################
echo Periksa pesan kesalahan di atas.
pause
exit /b 1

:success
echo.
echo #####################################
echo #   DEPENDENSI NODE.JS SIAP!        #
echo #####################################
echo Anda sekarang bisa melanjutkan ke langkah selanjutnya.
echo.
pause
