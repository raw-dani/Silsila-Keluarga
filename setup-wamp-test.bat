@echo off
REM ===================================================================
REM Setup Script for Testing Aplikasi Silsila Keluarga di WAMP Server
REM ===================================================================

echo ===========================================
echo   Setup Testing Aplikasi Silsila Keluarga
echo ===========================================
echo.

REM Check if we're in the right directory
if not exist "backend" (
    echo ERROR: Folder 'backend' tidak ditemukan!
    echo Pastikan Anda menjalankan script ini dari folder project utama.
    pause
    exit /b 1
)

if not exist "frontend" (
    echo ERROR: Folder 'frontend' tidak ditemukan!
    echo Pastikan Anda menjalankan script ini dari folder project utama.
    pause
    exit /b 1
)

echo [1/8] Checking prerequisites...
echo.

REM Check if PHP is available (try multiple paths)
set PHP_PATH=
if exist "C:\wamp64\bin\php\php8.2.0\php.exe" (
    set PHP_PATH="C:\wamp64\bin\php\php8.2.0\php.exe"
) else if exist "C:\wamp64\bin\php\php8.1.0\php.exe" (
    set PHP_PATH="C:\wamp64\bin\php\php8.1.0\php.exe"
) else if exist "C:\wamp64\bin\php\php8.0.0\php.exe" (
    set PHP_PATH="C:\wamp64\bin\php\php8.0.0\php.exe"
) else (
    php --version >nul 2>&1
    if %errorlevel% equ 0 (
        set PHP_PATH=php
    ) else (
        echo ERROR: PHP tidak ditemukan!
        echo Pastikan WAMP Server sudah terinstall.
        echo PHP paths yang dicari:
        echo - C:\wamp64\bin\php\php8.2.0\php.exe
        echo - C:\wamp64\bin\php\php8.1.0\php.exe
        echo - C:\wamp64\bin\php\php8.0.0\php.exe
        echo - atau PHP di PATH environment
        pause
        exit /b 1
    )
)
echo ✓ PHP ditemukan: %PHP_PATH%

REM Check if Composer is available
composer --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: Composer tidak ditemukan!
    echo Install Composer dari: https://getcomposer.org/
    pause
    exit /b 1
)
echo ✓ Composer ditemukan

REM Check if Node.js is available
node --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: Node.js tidak ditemukan!
    echo Install Node.js dari: https://nodejs.org/
    pause
    exit /b 1
)
echo ✓ Node.js ditemukan

REM Check if npm is available
npm --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: npm tidak ditemukan!
    echo npm biasanya terinstall bersama Node.js
    pause
    exit /b 1
)
echo ✓ npm ditemukan

echo.
echo [2/8] Setting up Laravel environment...
echo.

REM Setup Laravel .env
if not exist "backend\.env" (
    if exist "backend\.env.example" (
        copy backend\.env.example backend\.env
        echo ✓ File .env berhasil dibuat dari template
    ) else (
        echo WARNING: File .env.example tidak ditemukan, membuat .env kosong
        echo. > backend\.env
    )
) else (
    echo ✓ File .env sudah ada
)

REM Setup Frontend .env
if not exist "frontend\.env" (
    if exist "frontend\.env.example" (
        copy frontend\.env.example frontend\.env
        echo ✓ File frontend .env berhasil dibuat
    ) else (
        echo ℹ️  File frontend .env.example tidak ditemukan (optional)
    )
) else (
    echo ✓ File frontend .env sudah ada
)

echo.
echo [3/8] Installing PHP dependencies...
echo.

cd backend

REM Try to install with detected PHP path
%PHP_PATH% composer.phar install --no-dev --optimize-autoloader
if %errorlevel% neq 0 (
    REM Fallback to system composer if available
    composer install --no-dev --optimize-autoloader
    if %errorlevel% neq 0 (
        echo ERROR: Gagal install Composer dependencies!
        echo Coba jalankan manual:
        echo %PHP_PATH% composer.phar install --no-dev --optimize-autoloader
        cd ..
        pause
        exit /b 1
    )
)
echo ✓ Composer dependencies berhasil diinstall

echo.
echo [4/8] Generating Laravel application key...
echo.

%PHP_PATH% artisan key:generate
if %errorlevel% neq 0 (
    echo ERROR: Gagal generate application key!
    echo Coba jalankan manual: %PHP_PATH% artisan key:generate
    cd ..
    pause
    exit /b 1
)
echo ✓ Application key berhasil digenerate

echo.
echo [5/8] Installing Node.js dependencies...
echo.

cd ../frontend

REM Install npm dependencies (including jsPDF for PDF functionality)
npm install
if %errorlevel% neq 0 (
    echo ERROR: Gagal install npm dependencies!
    cd ..
    pause
    exit /b 1
)
echo ✓ npm dependencies berhasil diinstall (termasuk jsPDF dan html2canvas untuk fitur PDF)

echo.
echo [6/8] Building Vue.js frontend...
echo.

npm run build
if %errorlevel% neq 0 (
    echo ERROR: Gagal build Vue.js frontend!
    echo Coba jalankan manual di folder frontend:
    echo cd frontend
    echo npm run build
    echo Atau gunakan: npm run build:prod
    cd ..
    pause
    exit /b 1
)
echo ✓ Vue.js frontend berhasil di-build

echo.
echo [7/8] Copying build files to Laravel public...
echo.

if exist "dist" (
    REM Remove existing assets folder to ensure clean copy
    if exist "..\backend\public\assets" (
        rmdir /S /Q "..\backend\public\assets" 2>nul
    )

    REM Copy all files including assets folder
    xcopy /E /I /Y dist\* ..\backend\public\
    if %errorlevel% neq 0 (
        echo WARNING: Gagal copy files dengan xcopy
        echo Coba manual: xcopy /E /I /Y "%cd%\dist\*" "%cd%\..\backend\public\"
        echo Atau gunakan: robocopy "%cd%\dist" "%cd%\..\backend\public" /E /IS /IT
    ) else (
        echo ✓ Build files berhasil dicopy ke Laravel public
    )
) else (
    echo WARNING: Folder dist tidak ditemukan!
    echo Build Vue.js mungkin gagal
)

echo.
echo [8/8] Clearing Laravel cache and creating storage link...
echo.

cd ../backend

REM Create storage link (remove if exists)
if exist "public\storage" (
    rmdir "public\storage" 2>nul
)
%PHP_PATH% artisan storage:link

REM Clear Laravel caches
%PHP_PATH% artisan config:clear
if %errorlevel% neq 0 (
    echo WARNING: Gagal clear config cache
)
%PHP_PATH% artisan route:clear
if %errorlevel% neq 0 (
    echo WARNING: Gagal clear route cache
)
%PHP_PATH% artisan cache:clear
if %errorlevel% neq 0 (
    echo WARNING: Gagal clear application cache
)
%PHP_PATH% artisan view:clear
if %errorlevel% neq 0 (
    echo WARNING: Gagal clear view cache
)
%PHP_PATH% artisan route:cache
if %errorlevel% neq 0 (
    echo WARNING: Gagal cache route
)
echo ✓ Laravel cache berhasil dibersihkan dan storage link dibuat

echo.
echo ===========================================
echo        🎊 SETUP SELESAI! 🎊
echo ===========================================
echo.
echo Langkah selanjutnya:
echo.
echo 1. Setup database MySQL:
echo    - Buat database: silsila_keluarga
echo    - Buat user: silsila_user dengan password: password123
echo.
echo 2. Jalankan migrations:
echo    cd backend
echo    php artisan migrate --force
echo.
echo 3. Setup virtual domain di WAMP:
echo    - Edit httpd-vhosts.conf
echo    - Restart WAMP Server
echo.
echo 4. Test aplikasi:
echo    - Buka: http://tamin-supirah.googo.my.id
echo.
echo 5. Buat admin user (opsional):
echo    php artisan tinker
echo    User::create(['name'=>'Admin', 'email'=>'admin@tamin-supirah.googo.my.id', 'password'=>Hash::make('password123'), 'role'=>'admin']);
echo.
echo 📚 Lihat PRODUCTION-TEST.md untuk panduan lengkap!
echo.
pause
