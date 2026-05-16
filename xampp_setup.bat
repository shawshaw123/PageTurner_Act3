@echo off
title PageTurner Lab 6 - XAMPP Setup
color 0A

echo ========================================
echo    PageTurner Lab 6 - XAMPP Setup
echo ========================================
echo.

:: Check if we're in the right directory
if not exist "artisan" (
    echo ERROR: Please run this script from the Activity4 directory
    pause
    exit /b 1
)

echo [1/8] Checking XAMPP services...
echo.

:: Check if XAMPP is running
tasklist /FI "IMAGENAME eq httpd.exe" 2>NUL | find /I "httpd.exe" >NUL
if %ERRORLEVEL% NEQ 0 (
    echo WARNING: Apache (httpd.exe) is not running
    echo Please start Apache in XAMPP Control Panel
    pause
) else (
    echo ✓ Apache is running
)

tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL | find /I "mysqld.exe" >NUL
if %ERRORLEVEL% NEQ 0 (
    echo WARNING: MySQL (mysqld.exe) is not running
    echo Please start MySQL in XAMPP Control Panel
    pause
) else (
    echo ✓ MySQL is running
)

echo.
echo [2/8] Installing Composer dependencies...
echo.
composer install --no-interaction --optimize-autoloader

echo.
echo [3/8] Setting up environment...
echo.

if not exist ".env" (
    echo Copying XAMPP environment file...
    copy .env.xampp .env
    echo ✓ Environment file created
) else (
    echo ✓ Environment file already exists
)

echo.
echo [4/8] Generating application key...
echo.
php artisan key:generate --force

echo.
echo [5/8] Running database migrations...
echo.
php artisan migrate --force

echo.
echo [6/8] Creating storage directories...
echo.

if not exist "storage\app\imports" mkdir storage\app\imports
if not exist "storage\app\exports" mkdir storage\app\exports
if not exist "storage\app\backups" mkdir storage\app\backups
if not exist "storage\app\audit-archives" mkdir storage\app\audit-archives
if not exist "storage\app\reports" mkdir storage\app\reports

echo ✓ Storage directories created

echo.
echo [7/8] Running Lab 6 setup script...
echo.
php setup_lab6.php

echo.
echo [8/8] Testing Lab 6 features...
echo.
php test_lab6_features.php

echo.
echo ========================================
echo           Setup Complete!
echo ========================================
echo.
echo Your PageTurner Lab 6 is ready!
echo.
echo Access your application:
echo   - PHP Dev Server: http://localhost:8000
echo   - XAMPP Apache:   http://localhost/Activity4
echo.
echo Admin Login:
echo   Email: admin@pageturner.com
echo   Password: admin123
echo.
echo Next Steps:
echo   1. Start queue worker: php artisan queue:work
echo   2. Test import/export features
echo   3. Check admin dashboard
echo   4. Review documentation
echo.

:: Ask if user wants to start the development server
set /p start_server="Start PHP development server now? (y/n): "
if /i "%start_server%"=="y" (
    echo.
    echo Starting PHP development server...
    echo Press Ctrl+C to stop the server
    echo.
    php artisan serve --host=127.0.0.1 --port=8000
) else (
    echo.
    echo Setup completed! You can start the server manually with:
    echo   php artisan serve
    echo.
    pause
)
