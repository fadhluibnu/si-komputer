@echo off
title Development Servers Starter

:: =================================================================
::      KONFIGURASI SERVER (Ubah Port Jika Perlu)
:: =================================================================
SET LARAVEL_PORT=8000
:: =================================================================

cls
echo.
echo =================================================
echo ==       MEMULAI SERVER PENGEMBANGAN           ==
echo =================================================
echo.

REM ### LANGKAH 1: DETEKSI ALAMAT IP LOKAL ###
echo [1/4] Mendeteksi Alamat IP Lokal...
set "HOST_IP=127.0.0.1"
for /f "tokens=2 delims=:" %%a in ('ipconfig ^| findstr /i "IPv4"') do (
    for /f "tokens=*" %%b in ("%%a") do set "HOST_IP=%%b"
)
echo      -> Server akan berjalan di: %HOST_IP%
echo.
echo Skrip ini akan membuka 3 jendela terminal baru.
echo Mohon JANGAN TUTUP ketiga jendela tersebut selama pengembangan.
echo.
pause
cls

REM Pindah ke direktori tempat skrip ini berada (AplikasiWeb)
cd /d %~dp0

REM ### LANGKAH 2: MULAI MARIA DB SERVER ###
echo [2/4] Memulai MariaDB Server di jendela baru...
START "MariaDB Server" bin\mariadb\bin\mysqld.exe --console
REM Beri waktu 5 detik agar server database siap sepenuhnya
timeout /t 5 /nobreak > nul

REM Pindah ke folder proyek Laravel untuk menjalankan perintah selanjutnya
cd sistem

REM ### LANGKAH 3: MULAI LARAVEL ARTISAN SERVER ###
echo [3/4] Memulai Laravel Server di http://%HOST_IP%:%LARAVEL_PORT%
START "Laravel Backend" ..\bin\php\php.exe artisan serve --host=%HOST_IP% --port=%LARAVEL_PORT%

REM ### LANGKAH 4: MULAI VITE DEV SERVER ###
echo [4/4] Memulai Vite Frontend Server...
REM Menambahkan --host agar Vite juga bisa diakses dari jaringan
START "Vite Frontend" call ..\bin\nodejs\npm.cmd run dev -- --host

cd ..

echo.
echo ==========================================================
echo               SEMUA SERVER TELAH DIMULAI!
echo ==========================================================
echo.
echo - Buka browser dan akses: http://%HOST_IP%:%LARAVEL_PORT%
echo - Tiga jendela terminal telah dibuka. Biarkan tetap berjalan.
echo.
pause