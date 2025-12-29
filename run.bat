@echo off

echo ========================================
echo   MENJALANKAN APLIKASI SILSILA KELUARGA
echo ========================================
echo.

REM Path PHP
set PHP_EXE=C:\wamp64\bin\php\php8.3.14\php.exe

echo 🔧 MENJALANKAN BACKEND...
echo.

echo Memastikan database sudah siap...
%PHP_EXE% backend\artisan migrate --force >nul 2>&1
if %errorlevel% neq 0 (
    echo Error: Migration gagal. Pastikan database MySQL sudah aktif.
    echo Warning: Backend tidak dapat dijalankan.
    echo.
    goto run_frontend_only
)
echo Ok: Database migration berhasil
echo.

echo Menjalankan database seeder untuk admin user...
%PHP_EXE% backend\artisan db:seed --class=AdminUserSeeder --force >nul 2>&1
if %errorlevel% neq 0 (
    echo Error Seeder gagal.
    echo Warning: Melanjutkan tanpa admin user.
) else (
    echo Ok: Admin user berhasil dibuat
)
echo.

echo Menjalankan backend server...
echo - Backend API: http://localhost:8000
echo - Admin login: admin@silsila-keluarga.com / admin123
echo.
start "Laravel Backend" cmd /k "%PHP_EXE% backend\artisan serve --host=127.0.0.1 --port=8000"

echo Ok: Backend berhasil dijalankan!
echo.

REM Backend berhasil, jalankan frontend
:run_frontend
echo MENJALANKAN FRONTEND...
echo.
cd frontend
echo Menjalankan frontend development server...
echo - Frontend: http://localhost:5173
echo.
start "Vue.js Frontend" cmd /k "npm run dev"
cd ..
echo Ok: Frontend berhasil dijalankan!
echo.
goto show_full_status

:run_frontend_only
REM Jika backend gagal, jalankan frontend saja
echo MENJALANKAN FRONTEND SAJA...
echo.
cd frontend
echo Menjalankan frontend development server...
echo - Frontend: http://localhost:5173
echo Warning: Backend tidak tersedia - frontend hanya untuk tampilan saja
echo.
start "Vue.js Frontend" cmd /k "npm run dev"
cd ..
echo Ok: Frontend berhasil dijalankan!
echo.
goto show_frontend_only_status

:show_full_status
echo ========================================
echo     APLIKASI LENGKAP BERJALAN
echo ========================================
echo.
echo - Backend API: http://localhost:8000
echo - Frontend UI: http://localhost:5173
echo.
echo Admin credentials:
echo    Email: admin@silsila-keluarga.com
echo    Password: admin123
echo.
goto show_commands

:show_frontend_only_status
echo ========================================
echo     FRONTEND BERJALAN
echo ========================================
echo.
echo Backend: Tidak tersedia
echo Frontend UI: http://localhost:5173
echo Warning: Hanya untuk tampilan - fungsi terbatas
echo.
goto show_commands

:show_commands
echo Untuk melihat daftar API routes, jalankan:
echo %PHP_EXE% backend\artisan route:list --path=api
echo.

:final_message
echo ========================================
echo.

pause
