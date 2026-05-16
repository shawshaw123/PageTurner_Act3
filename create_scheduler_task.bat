@echo off
title PageTurner Lab 6 - Create Windows Scheduler Task
color 0E

echo ========================================
echo PageTurner Lab 6 - Windows Scheduler Setup
echo ========================================
echo.

:: Check if we're in the right directory
if not exist "artisan" (
    echo ERROR: Please run this script from the Activity4 directory
    pause
    exit /b 1
)

echo This will create a Windows Task Scheduler job
echo to run the Laravel scheduler every minute.
echo.

:: Get the current directory
set "CURRENT_DIR=%CD%"
set "PHP_PATH=C:\xampp\php\php.exe"

echo Current Directory: %CURRENT_DIR%
echo PHP Path: %PHP_PATH%
echo.

:: Check if PHP exists at the specified path
if not exist "%PHP_PATH%" (
    echo ERROR: PHP not found at %PHP_PATH%
    echo Please update the PHP_PATH variable in this script
    pause
    exit /b 1
)

set /p confirm="Create Windows Scheduler task? (y/n): "
if /i not "%confirm%"=="y" (
    echo Operation cancelled.
    pause
    exit /b 0
)

echo.
echo Creating Windows Scheduler task...
echo.

:: Create the scheduled task
schtasks /create /tn "Laravel Scheduler - PageTurner" ^
/tr "\"%PHP_PATH%\" \"%CURRENT_DIR%\artisan\" schedule:run" ^
/sc minute ^
/ru SYSTEM ^
/rl HIGHEST ^
/f

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ✓ SUCCESS: Windows Scheduler task created!
    echo.
    echo Task Details:
    echo   Name: Laravel Scheduler - PageTurner
    echo   Trigger: Every minute
    echo   User: SYSTEM
    echo   Command: %PHP_PATH% artisan schedule:run
    echo.
    echo The scheduler will now run automatically every minute.
    echo You can manage this task in Windows Task Scheduler.
    echo.
    echo To delete this task later, run:
    echo   schtasks /delete /tn "Laravel Scheduler - PageTurner" /f
) else (
    echo.
    echo ✗ ERROR: Failed to create scheduled task
    echo Please run this script as Administrator
)

echo.
pause
