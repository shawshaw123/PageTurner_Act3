@echo off
title PageTurner Lab 6 - Queue Worker
color 0B

echo ========================================
echo   PageTurner Lab 6 - Queue Worker
echo ========================================
echo.

:: Check if we're in the right directory
if not exist "artisan" (
    echo ERROR: Please run this script from the Activity4 directory
    pause
    exit /b 1
)

echo Starting Laravel Queue Worker...
echo This will process background jobs for:
echo   - Import/Export operations
echo   - Email notifications
echo   - Backup operations
echo   - Report generation
echo.

echo Press Ctrl+C to stop the queue worker
echo.

:: Start the queue worker with timeout 0 (runs forever)
php artisan queue:work --timeout=0 --tries=3 --memory=512

echo.
echo Queue worker stopped.
pause
