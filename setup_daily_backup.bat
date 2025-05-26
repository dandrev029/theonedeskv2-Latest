@echo off
REM Setup Daily Backup Task for TheOneDesk Database
REM This script creates a Windows scheduled task to run daily backups

echo Setting up daily database backup task...

REM Create the scheduled task to run daily at 2 AM
schtasks /create /tn "TheOneDesk Daily Backup" /tr "php artisan backup:database" /sc daily /st 02:00 /f /ru "SYSTEM" /rl highest /sd %date%

if %errorlevel% equ 0 (
    echo Daily backup task created successfully!
    echo The database will be backed up automatically every day at 2:00 AM
    echo Backup location: C:\Users\Sta Rosa\Desktop\TheOneDesk Daily Backups\Database Backups
) else (
    echo Failed to create scheduled task. Please run as administrator.
)

echo.
echo You can also run manual backups anytime using:
echo php artisan backup:database

pause
